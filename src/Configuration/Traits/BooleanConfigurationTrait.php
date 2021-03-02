<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Traits;

use Symfony\Component\Console\Input\InputOption;

trait BooleanConfigurationTrait
{
    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function isValueValid($value): bool
    {
        return in_array($value, ['true', 'false']);
    }

    /**
     * @param mixed $validatedValue
     * @return bool
     */
    public function cast($validatedValue): bool
    {
        switch ($validatedValue) {
            case 'true':
                return true;
            case 'false':
                return false;
        }
    }
}
