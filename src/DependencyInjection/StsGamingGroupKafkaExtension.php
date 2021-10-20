<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\DependencyInjection;

use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ProducerInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Decoder\Contract\DecoderInterface;
use StsGamingGroup\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;
use StsGamingGroup\KafkaBundle\Validator\Contract\ValidatorInterface;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class StsGamingGroupKafkaExtension extends ConfigurableExtension implements CompilerPassInterface
{
    private const XML_CONFIGS = [
        'rd_kafka',
        'consumers',
        'commands',
        'configurations',
        'configuration_types',
        'decoders',
        'producers',
        'denormalizers',
        'validators',
    ];

    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        foreach (self::XML_CONFIGS as $xmlFile) {
            $loader->load(sprintf($xmlFile . '.xml'));
        }

        $container->registerForAutoconfiguration(ConsumerInterface::class)
            ->addTag('sts_gaming_group_kafka.kafka.consumer');

        $container->registerForAutoconfiguration(ProducerInterface::class)
            ->addTag('sts_gaming_group_kafka.kafka.producer');

        $container->registerForAutoconfiguration(ConfigurationInterface::class)
            ->addTag('sts_gaming_group_kafka.configuration.type');

        $container->registerForAutoconfiguration(DecoderInterface::class)
            ->addTag('sts_gaming_group_kafka.decoder');

        $container->registerForAutoconfiguration(DenormalizerInterface::class)
            ->addTag('sts_gaming_group_kafka.denormalizer');

        $container->registerForAutoconfiguration(ValidatorInterface::class)
            ->addTag('sts_gaming_group_kafka.validator');

        $configurationResolver = $container->getDefinition('sts_gaming_group_kafka.configuration.configuration_resolver');
        $configurationResolver->setArgument(1, $mergedConfig);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->addConsumersAndProvider($container);
        $this->addProducersAndProvider($container);
        $this->addConfigurations($container);
    }

    private function addConsumersAndProvider(ContainerBuilder $container): void
    {
        $providerId = 'sts_gaming_group_kafka.client.consumer.consumer_provider';
        if (!$container->has($providerId)) {
            throw new InvalidDefinitionException(
                sprintf('Unable to find any consumer provider. Looking for service id %s', $providerId)
            );
        }

        $consumerProvider = $container->findDefinition($providerId);
        $consumers = $container->findTaggedServiceIds('sts_gaming_group_kafka.kafka.consumer');
        foreach ($consumers as $id => $tags) {
            $consumerProvider->addMethodCall('addConsumer', [new Reference($id)]);
        }
    }

    private function addProducersAndProvider(ContainerBuilder $container): void
    {
        $providerId = 'sts_gaming_group_kafka.client.producer.producer_provider';
        if (!$container->has($providerId)) {
            throw new InvalidDefinitionException(
                sprintf('Unable to find any producer provider. Looking for service id %s', $providerId)
            );
        }

        $producerProvider = $container->findDefinition($providerId);
        $producers = $container->findTaggedServiceIds('sts_gaming_group_kafka.kafka.producer');
        foreach ($producers as $id => $tags) {
            $producerProvider->addMethodCall('addProducer', [new Reference($id)]);
        }
    }

    private function addConfigurations(ContainerBuilder $container): void
    {
        $configurationsId = 'sts_gaming_group_kafka.configuration.raw_configuration';
        if (!$container->has($configurationsId)) {
            throw new InvalidDefinitionException(
                sprintf('Unable to find configurations class. Looking for service id %s', $configurationsId)
            );
        }

        $configurations = $container->findDefinition($configurationsId);
        $configurationTypes = $container->findTaggedServiceIds('sts_gaming_group_kafka.configuration.type');
        foreach ($configurationTypes as $id => $tags) {
            $configurations->addMethodCall('addConfiguration', [new Reference($id)]);
        }
    }
}
