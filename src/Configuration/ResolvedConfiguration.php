<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Contract\GlobalConfigurationInterface;
use Sts\KafkaBundle\Configuration\Contract\TopicConfigurationInterface;

class ResolvedConfiguration
{
    private array $configurations = [];

    /**
     * @param mixed $resolvedValue
     * @param ConfigurationInterface $configuration
     * @return ResolvedConfiguration
     */
    public function addConfiguration(ConfigurationInterface $configuration, $resolvedValue): self
    {
        $this->configurations[$configuration->getName()] = [
            'configuration' => $configuration,
            'resolvedValue' => $resolvedValue
        ];

        return $this;
    }

    /**
     * @return array<ConfigurationInterface|mixed>
     */
    public function getConfigurations(): array
    {
        return $this->configurations;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getConfigurationValue(string $name)
    {
        return $this->configurations[$name]['resolvedValue'];
    }

    /**
     * @return array<TopicConfigurationInterface|mixed>
     */
    public function getTopicConfigurations(): array
    {
        $topicConfigurations = [];
        foreach ($this->configurations as $configuration) {
            if ($configuration['configuration'] instanceof TopicConfigurationInterface) {
                $topicConfigurations[] = $configuration;
            }
        }

        return $topicConfigurations;
    }

    public function getGlobalConfigurations(): array
    {
        $globalConfigurations = [];
        foreach ($this->configurations as $configuration) {
            if ($configuration['configuration'] instanceof GlobalConfigurationInterface) {
                $globalConfigurations[] = $configuration;
            }
        }

        return $globalConfigurations;
    }
}
