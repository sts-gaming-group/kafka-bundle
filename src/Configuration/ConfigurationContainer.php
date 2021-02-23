<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration;

class ConfigurationContainer
{
    private array $configurations = [];

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addConfiguration(string $name, $value): self
    {
        $this->configurations[$name] = $value;

        return $this;
    }

    public function getConfigurations(): array
    {
        return $this->configurations;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getConfiguration(string $name)
    {
        return $this->configurations[$name];
    }

    public function exists(string $name): bool
    {
        return array_key_exists($name, $this->configurations);
    }
}
