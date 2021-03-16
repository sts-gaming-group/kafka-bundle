<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration;

use Sts\KafkaBundle\Client\Contract\ClientInterface;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Client\Contract\ProducerInterface;
use Sts\KafkaBundle\Configuration\Contract\CastValueInterface;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationResolver
{
    private RawConfiguration $rawConfiguration;
    private array $yamlConfig;

    public function __construct(RawConfiguration $rawConfiguration, array $yamlConfig)
    {
        $this->rawConfiguration = $rawConfiguration;
        $this->yamlConfig = $yamlConfig;
    }

    /**
     * @param string|ConsumerInterface $clientClass
     * @param InputInterface|null $input
     * @return ResolvedConfiguration
     */
    public function resolve($clientClass, ?InputInterface $input = null): ResolvedConfiguration
    {
        $resolvedConfiguration = new ResolvedConfiguration();

        foreach ($this->rawConfiguration->getConfigurations() as $configuration) {
            $resolvedValue = $this->getResolvedValue($configuration, $clientClass, $input);
            if ($configuration instanceof CastValueInterface) {
                $resolvedValue = $configuration->cast($resolvedValue);
            }
            $resolvedConfiguration->addConfiguration($configuration, $resolvedValue);
        }

        return $resolvedConfiguration;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param string|ClientInterface $clientClass
     * @param InputInterface|null $input
     * @return mixed
     */
    private function getResolvedValue(
        ConfigurationInterface $configuration,
        $clientClass,
        ?InputInterface $input
    ) {
        $name = $configuration->getName();

        if ($input && $input->getParameterOption('--' . $name) !== false) {
            $resolvedValue = $input->getOption($name);
            $this->validateResolvedValue($configuration, $resolvedValue);

            return $resolvedValue;
        }

        $configName = '';
        if (is_a($clientClass, ConsumerInterface::class, true)) {
            $configName = 'consumers';
        }

        if (is_a($clientClass, ProducerInterface::class, true)) {
            $configName = 'producers';
        }

        if (!$configName) {
            throw new \RuntimeException(sprintf(
                'Object must implement %s or %s to properly resolve configuration.',
                ConsumerInterface::class,
                ProducerInterface::class
            ));
        }

        $className = is_string($clientClass) ? $clientClass : get_class($clientClass);

        if (isset($this->yamlConfig[$configName]['instances'][$className][$name]) &&
            $this->yamlConfig[$configName]['instances'][$className][$name] !== $configuration::getDefaultValue()) {
            $resolvedValue = $this->yamlConfig[$configName]['instances'][$className][$name];
            $this->validateResolvedValue($configuration, $resolvedValue);

            return $resolvedValue;
        }

        $resolvedValue = $this->yamlConfig[$configName][$name] ??
            $this->yamlConfig[$name] ??
            $configuration::getDefaultValue();

        $this->validateResolvedValue($configuration, $resolvedValue);

        return $resolvedValue;
    }

    private function validateResolvedValue(ConfigurationInterface $configuration, $resolvedValue): void
    {
        if (!$configuration->isValueValid($resolvedValue)) {
            throw new InvalidConfigurationException(sprintf(
                'Invalid option passed for %s. Passed value `%s`. Configuration description: %s',
                $configuration->getName(),
                is_array($resolvedValue) ? implode(', ', $resolvedValue) : $resolvedValue,
                $configuration->getDescription()
            ));
        }
    }
}
