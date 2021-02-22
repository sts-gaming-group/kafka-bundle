<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Sts\KafkaBundle\Configuration\Contract\ValidatedConfigurationInterface;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationResolver
{
    private RawConfigurations $rawConfigurations;
    private array $config;

    public function __construct(RawConfigurations $rawConfigurations, array $config)
    {
        $this->rawConfigurations = $rawConfigurations;
        $this->config = $config;
    }

    public function resolve(ConsumerInterface $consumer, InputInterface $input): ConfigurationContainer
    {
        $configurationContainer = new ConfigurationContainer();

        foreach ($this->rawConfigurations->getConfigurations() as $configuration) {
            $name = $configuration->getName();
            $value = $this->getValue($configuration, $consumer, $input, $this->config);
            $configurationContainer->addConfiguration($name, $value);
        }

        foreach ($this->rawConfigurations->getConfigurations() as $configuration) {
            if ($configuration instanceof ValidatedConfigurationInterface &&
                !$configuration->validate($configurationContainer)) {
                throw new InvalidConfigurationException(
                    $configuration->validationError($configurationContainer) ?:
                        sprintf('Invalid configuration %s', get_class($configuration))
                );
            }
        }

        return $configurationContainer;
    }

    private function getValue(
        ConfigurationInterface $configuration,
        ConsumerInterface $consumer,
        InputInterface $input,
        array $config
    ) {
        $name = $configuration->getName();
        $consumerClass = get_class($consumer);
        if ($input->hasOption($name) && null !== $input->getOption($name) && [] !== $input->getOption($name)) {
            return $input->getOption($name);
        }

        if ($this->isConfigurationSetForConsumer($config, $name, $consumerClass)) {
            return $config['consumers'][$consumerClass][$name];
        }

        if ($this->isGlobalConfigurationSet($config, $name)) {
            return $config[$name];
        }

        $defaultValue = $configuration->getDefaultValue();
        if ('' === $defaultValue || [] === $defaultValue) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Set `%s` configuration either in global config, consumer definition or pass it as an option in CLI.',
                    $name
                )
            );
        }

        return $defaultValue;
    }

    private function isConfigurationSetForConsumer(array $config, string $configuration, string $consumer): bool
    {
        return array_key_exists($consumer, $config['consumers']) &&
            array_key_exists($configuration, $config['consumers'][$consumer]) &&
            $config['consumers'][$consumer][$configuration] &&
            null !== $config[$configuration] &&
            [] !== $config[$configuration];
    }

    private function isGlobalConfigurationSet(array $config, string $configuration): bool
    {
        return array_key_exists($configuration, $config) &&
            $config[$configuration] &&
            null !== $config[$configuration] &&
            [] !== $config[$configuration];
    }
}
