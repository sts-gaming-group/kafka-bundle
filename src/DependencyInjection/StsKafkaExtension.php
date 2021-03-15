<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\DependencyInjection;

use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Client\Contract\ProducerHandlerInterface;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Sts\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class StsKafkaExtension extends ConfigurableExtension implements CompilerPassInterface
{
    private const XML_CONFIGS = [
        'rd_kafka_factories',
        'factories',
        'consumers',
        'commands',
        'configurations',
        'configuration_types',
        'decoders',
        'producers',
        'denormalizers'
    ];

    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        foreach (self::XML_CONFIGS as $xmlFile) {
            $loader->load(sprintf($xmlFile . '.xml'));
        }

        $container->registerForAutoconfiguration(ConsumerInterface::class)
            ->addTag('sts_kafka.kafka.consumer');

        $container->registerForAutoconfiguration(ProducerHandlerInterface::class)
            ->addTag('sts_kafka.kafka.producer.handler');

        $container->registerForAutoconfiguration(ConfigurationInterface::class)
            ->addTag('sts_kafka.configuration.type');

        $container->registerForAutoconfiguration(DecoderInterface::class)
            ->addTag('sts_kafka.decoder');

        $container->registerForAutoconfiguration(DenormalizerInterface::class)
            ->addTag('sts_kafka.denormalizer');

        $configurationResolver = $container->getDefinition('sts_kafka.configuration.configuration_resolver');
        $configurationResolver->setArgument(1, $mergedConfig);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->addConsumersAndProvider($container);
        $this->addProducersAndProvider($container);
        $this->addConfigurations($container);
        $this->addDecoders($container);
    }

    private function addConsumersAndProvider(ContainerBuilder $container): void
    {
        $providerId = 'sts_kafka.client.consumer.consumer_provider';
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

    private function addProducersAndProvider(ContainerBuilder $container): void
    {
        $providerId = 'sts_kafka.client.producer.producer_handler_provider';
        if (!$container->has($providerId)) {
            throw new InvalidDefinitionException(
                sprintf('Unable to find any producer provider. Looking for service id %s', $providerId)
            );
        }

        $producerProvider = $container->findDefinition($providerId);
        $producers = $container->findTaggedServiceIds('sts_kafka.kafka.producer.handler');
        foreach ($producers as $id => $tags) {
            $producerProvider->addMethodCall('addHandler', [new Reference($id)]);
        }
    }

    private function addConfigurations(ContainerBuilder $container): void
    {
        $configurationsId = 'sts_kafka.configuration.raw_configuration';
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
        $decoderFactoryId = 'sts_kafka.factory.decoder_factory';
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
