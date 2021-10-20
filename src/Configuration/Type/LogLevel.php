<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\CastValueInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\KafkaConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ProducerConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class LogLevel implements KafkaConfigurationInterface, ConsumerConfigurationInterface, ProducerConfigurationInterface, CastValueInterface
{
    public const NAME = 'log_level';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getKafkaProperty(): string
    {
        return 'log_level';
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Logging level (syslog(3) levels). Defaults to LOG_ERR (%s)',
            $this->getDefaultValue()
        );
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && strpos((string) $value, '.') === false;
    }

    public function cast($validatedValue): int
    {
        return (int) $validatedValue;
    }

    public function getDefaultValue(): int
    {
        return LOG_ERR;
    }
}
