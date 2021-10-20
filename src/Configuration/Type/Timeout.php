<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\CastValueInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
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
            $this->getDefaultValue()
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

    public function getDefaultValue(): int
    {
        return 1000;
    }
}
