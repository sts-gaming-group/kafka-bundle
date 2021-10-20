<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\CastValueInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class MaxRetryDelay implements ConsumerConfigurationInterface, CastValueInterface
{
    public const NAME = 'max_retry_delay';

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
        return sprintf('Maximum retry delay in ms. Defaults to %s', $this->getDefaultValue());
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && strpos((string) $value, '.') === false && $value >= 0;
    }

    public function getDefaultValue(): int
    {
        return 2000;
    }

    public function cast($validatedValue): int
    {
        return (int) $validatedValue;
    }
}
