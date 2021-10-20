<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Traits;

trait ObjectConfigurationTrait
{
    abstract protected function getInterface(): string;

    public function isValueValid($value): bool
    {
        $interface = $this->getInterface();

        if (!is_array($value)) {
            return $this->doValidate($interface, $value);
        }

        if (empty($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!$this->doValidate($interface, $item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $interface
     * @param mixed $item
     * @return bool
     */
    private function doValidate(string $interface, $item): bool
    {
        if (is_object($item)) {
            return in_array($interface, class_implements($item), true);
        }

        if (is_string($item)) {
            return class_exists($item) && in_array($interface, class_implements($item), true);
        }

        return false;
    }
}
