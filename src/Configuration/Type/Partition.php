<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\CastValueInterface;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Partition implements ConfigurationInterface, CastValueInterface
{
    public const NAME = 'partition';
    public const DEFAULT_VALUE = 0;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getDescription(): string
    {
        return sprintf(
            <<<EOT
        Which partition consumer should consume from. Defaults to %s. 
        Must be an integer equal to or greater than 0.
        EOT,
            self::DEFAULT_VALUE
        );
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && $value >= 0;
    }

    public function cast($validatedValue): int
    {
        return (int) $validatedValue;
    }
}
