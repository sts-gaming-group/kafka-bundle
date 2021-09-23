<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Traits;

use Symfony\Component\Console\Input\InputOption;

trait ObjectConfigurationTrait
{
    abstract protected function getInterface(): string;

    public function isValueValid($value): bool
    {
        $interface = $this->getInterface();

        if (is_object($value)) {
            return in_array($interface, class_implements($value), true);
        }

        if (is_string($value)) {
            return class_exists($value) && in_array($interface, class_implements($value), true);
        }

        return false;
    }
}
