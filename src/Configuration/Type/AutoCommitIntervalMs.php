<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\TopicConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class AutoCommitIntervalMs implements TopicConfigurationInterface
{
    public const NAME = 'auto_commit_interval_ms';
    public const DEFAULT_VALUE = '1000';

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
            'The frequency in milliseconds that the consumer offsets are auto-committed to Kafka if enable_auto_commit is set to true. 
            Defaults to %s. Must be a numeric string.',
            self::DEFAULT_VALUE
        );
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && is_string($value);
    }
}
