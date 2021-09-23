<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\CastValueInterface;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Timeout implements ConsumerConfigurationInterface, CastValueInterface
{
    public const NAME = 'timeout';

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
            'Maximum amount of time to wait for a message to be received. Defaults to %s ms.',
            self::getDefaultValue()
        );
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && strpos((string) $value, '.') === false && $value >= 0;
    }

    public function cast($validatedValue): int
    {
        return (int) $validatedValue;
    }

    public static function getDefaultValue(): int
    {
        return 1000;
    }
}
