<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\DependencyInjection;

use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class StsKafkaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        foreach (['consumers', 'commands'] as $file) {
            $loader->load(\sprintf('%s.xml', $file));
        }

        $container->registerForAutoconfiguration(ConsumerInterface::class)
            ->addTag('sts_kafka.kafka.consumer');
    }
}
