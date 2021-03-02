<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\DependencyInjection;

use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class StsKafkaExtension extends Extension implements CompilerPassInterface
{
    private const XML_CONFIGS = [
        'factories',
        'consumers',
        'commands',
        'configurations',
        'configuration_types',
        'decoders',
        'producers'
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        foreach (self::XML_CONFIGS as $xmlFile) {
            $loader->load(sprintf($xmlFile . '.xml'));
        }

        $container->registerForAutoconfiguration(ConsumerInterface::class)
            ->addTag('sts_kafka.kafka.consumer');

        $container->registerForAutoconfiguration(ConfigurationInterface::class)
            ->addTag('sts_kafka.configuration.type');

        $container->registerForAutoconfiguration(DecoderInterface::class)
            ->addTag('sts_kafka.decoder');

        $configurationResolver = $container->getDefinition('sts_kafka.configuration.resolver');
        $configurationResolver->setArgument(1, $config);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->addConsumersAndProvider($container);
        $this->addConfigurations($container);
        $this->addDecoders($container);
    }

    private function addConsumersAndProvider(ContainerBuilder $container): void
    {
        $providerId = 'sts_kafka.client.consumer.provider';
        if (!$container->has($providerId)) {
            throw new InvalidDefinitionException(
                sprintf('Unable to find any consumer provider. Looking for service id %s', $providerId)
            );
        }

        $consumerProvider = $container->findDefinition($providerId);
        $consumers = $container->findTaggedServiceIds('sts_kafka.kafka.consumer');
        foreach ($consumers as $id => $tags) {
            $consumerProvider->addMethodCall('addConsumer', [new Reference($id)]);
        }
    }

    private function addConfigurations(ContainerBuilder $container): void
    {
        $configurationsId = 'sts_kafka.raw.configuration';
        if (!$container->has($configurationsId)) {
            throw new InvalidDefinitionException(
                sprintf('Unable to find configurations class. Looking for service id %s', $configurationsId)
            );
        }

        $configurations = $container->findDefinition($configurationsId);
        $configurationTypes = $container->findTaggedServiceIds('sts_kafka.configuration.type');
        foreach ($configurationTypes as $id => $tags) {
            $configurations->addMethodCall('addConfiguration', [new Reference($id)]);
        }
    }

    private function addDecoders(ContainerBuilder $container): void
    {
        $decoderFactoryId = 'sts_kafka.decoder.factory';
        if (!$container->has($decoderFactoryId)) {
            throw new InvalidDefinitionException(
                sprintf('Unable to find decoder factory class. Looking for service id %s', $decoderFactoryId)
            );
        }

        $decoderFactory = $container->findDefinition($decoderFactoryId);
        $decoders = $container->findTaggedServiceIds('sts_kafka.decoder');
        foreach ($decoders as $id => $tags) {
            $decoderFactory->addMethodCall('addDecoder', [new Reference($id)]);
        }
    }
}
