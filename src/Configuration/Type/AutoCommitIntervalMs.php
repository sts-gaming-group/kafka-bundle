<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\KafkaConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class AutoCommitIntervalMs implements ConsumerConfigurationInterface, KafkaConfigurationInterface
{
    public const NAME = 'auto_commit_interval_ms';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getKafkaProperty(): string
    {
        return 'auto.commit.interval.ms';
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getDescription(): string
    {
        return sprintf(
            <<<EOT
            The frequency in milliseconds that the consumer offsets are auto-committed to Kafka.
            Enable auto commit must be set to true. Defaults to %s. Must be a numeric string.
            EOT,
            $this->getDefaultValue()
        );
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && is_string($value);
    }

    public function getDefaultValue(): string
    {
        return '50';
    }
}
