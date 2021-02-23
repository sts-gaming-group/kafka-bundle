<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration;

use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;

class RawConfigurations
{
    /**
     * @var array<ConfigurationInterface>
     */
    private array $configurations = [];

    /**
     * @param ConfigurationInterface $configuration
     * @return $this
     */
    public function addConfiguration(ConfigurationInterface $configuration): self
    {
        $this->validateConfiguration($configuration);
        $this->configurations[$configuration->getName()] = $configuration;

        return $this;
    }

    /**
     * @return array<ConfigurationInterface>
     */
    public function getConfigurations(): array
    {
        return $this->configurations;
    }

    public function getConfigurationByName(string $name): ConfigurationInterface
    {
        if (array_key_exists($name, $this->configurations)) {
            return $this->configurations[$name];
        }

        throw new InvalidConfigurationException(sprintf('Configuration with name %s does not exist.', $name));
    }

    public function configurationExists(string $name): bool
    {
        return array_key_exists($name, $this->configurations);
    }

    private function validateConfiguration(ConfigurationInterface $configuration): void
    {
        if (array_key_exists($configuration->getName(), $this->configurations)) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Configuration with name `%s` has already been registered',
                    $configuration->getName()
                )
            );
        }
    }
}
