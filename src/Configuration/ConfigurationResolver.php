<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration;

use StsGamingGroup\KafkaBundle\Client\Contract\ClientInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ProducerInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\CastValueInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Exception\InvalidClientException;
use StsGamingGroup\KafkaBundle\Configuration\Exception\InvalidConfigurationException;
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
     * @param string|ClientInterface $clientClass
     * @param InputInterface|null $input
     * @return ResolvedConfiguration
     */
    public function resolve($clientClass, ?InputInterface $input = null): ResolvedConfiguration
    {
        $configuration = new ResolvedConfiguration();

        foreach ($this->rawConfiguration->getConfigurations() as $rawConfiguration) {
            $resolvedValue = $this->getResolvedValue($rawConfiguration, $clientClass, $input);

            if ($rawConfiguration instanceof CastValueInterface) {
                $resolvedValue = $rawConfiguration->cast($resolvedValue);
            }

            $configuration->addConfiguration($rawConfiguration, $resolvedValue);
        }

        return $configuration;
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
        $type = '';
        if (is_a($clientClass, ConsumerInterface::class, true)) {
            $type = 'consumers';
        }

        if (is_a($clientClass, ProducerInterface::class, true)) {
            $type = 'producers';
        }

        if (!$type) {
            throw new InvalidClientException(sprintf(
                'Object must implement %s or %s to properly resolve configuration.',
                ConsumerInterface::class,
                ProducerInterface::class
            ));
        }

        $name = $configuration->getName();
        if ($input && $input->getParameterOption('--' . $name) !== false) {
            $resolvedValue = $input->getOption($name);
            $this->validateResolvedValue($configuration, $resolvedValue);

            return $resolvedValue;
        }

        $clientClass = is_string($clientClass) ? $clientClass : get_class($clientClass);
        if ($this->shouldResolveInstance($clientClass, $type, $configuration)) {
            $resolvedValue = $this->yamlConfig[$type]['instances'][$clientClass][$name];
            $this->validateResolvedValue($configuration, $resolvedValue);

            return $resolvedValue;
        }

        $parentClass = $this->getParentClass($clientClass);
        if ($this->shouldResolveInstance($parentClass, $type, $configuration)) {
            $resolvedValue = $this->yamlConfig[$type]['instances'][$parentClass][$name];
            $this->validateResolvedValue($configuration, $resolvedValue);

            return $resolvedValue;
        }

        return $configuration->getDefaultValue();
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param mixed $resolvedValue
     */
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

    /**
     * @param string|ClientInterface $clientClass
     * @return string
     */
    private function getParentClass($clientClass): string
    {
        $parentClass = get_parent_class($clientClass);

        return $parentClass === false ? '' : $parentClass;
    }

    private function shouldResolveInstance(string $class, string $type, ConfigurationInterface $configuration): bool
    {
        $name = $configuration->getName();

        return isset($this->yamlConfig[$type]['instances'][$class][$name]) &&
            $this->yamlConfig[$type]['instances'][$class][$name];
    }
}
