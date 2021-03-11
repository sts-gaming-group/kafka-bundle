<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration;

use Sts\KafkaBundle\Client\Contract\ClientInterface;
use Sts\KafkaBundle\Configuration\Contract\CastValueInterface;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Sts\KafkaBundle\Client\Contract\ProducerInterface;
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

    public function resolve(ClientInterface $client, ?InputInterface $input = null): ResolvedConfiguration
    {
        $resolvedConfiguration = new ResolvedConfiguration();

        foreach ($this->rawConfiguration->getConfigurations() as $configuration) {
            $resolvedValue = $this->getResolvedValue($configuration, $client, $input);
            if ($configuration instanceof CastValueInterface) {
                $resolvedValue = $configuration->cast($resolvedValue);
            }
            $resolvedConfiguration->addConfiguration($configuration, $resolvedValue);
        }

        return $resolvedConfiguration;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param ClientInterface $client
     * @param InputInterface|null $input
     * @return mixed
     */
    private function getResolvedValue(
        ConfigurationInterface $configuration,
        ClientInterface $client,
        ?InputInterface $input
    ) {
        $name = $configuration->getName();

        if ($input && $input->getParameterOption('--' . $name) !== false) {
            $value = $input->getOption($name);
            if (!$configuration->isValueValid($value)) {
                throw new InvalidConfigurationException(sprintf(
                    'Invalid option passed for %s. Passed value `%s`. Configuration description: %s',
                    $name,
                    is_array($value) ? implode(', ', $value) : $value,
                    $configuration->getDescription()
                ));
            }

            return $value;
        }

        if ($client instanceof ConsumerInterface) {
            $configName = 'consumers';
        } elseif ($client instanceof ProducerInterface) {
            $configName = 'producers';
        } else {
            throw new \RuntimeException(sprintf(
                'Object must implement %s or %s to properly resolve configuration.',
                ConsumerInterface::class,
                ProducerInterface::class
            ));
        }

        $className = get_class($client);
        if (isset($this->yamlConfig[$configName][$className][$name]) &&
            $this->yamlConfig[$configName][$className][$name] !== $configuration::getDefaultValue()) {
            return $this->yamlConfig[$configName][$className][$name];
        }

        if (isset($this->yamlConfig[$name])) {
            return $this->yamlConfig[$name];
        }

        throw new InvalidConfigurationException(
            sprintf(
                'Set `%s` configuration either in global config, consumer definition or pass it as an option in CLI.',
                $name
            )
        );
    }
}
