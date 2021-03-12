<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\CastValueInterface;
use Sts\KafkaBundle\Configuration\Contract\ProducerConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class ProducerFlushRetries implements ProducerConfigurationInterface, CastValueInterface
{
    public const NAME = 'producer_flush_retries';

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
        return
            <<<EOT
        How many times Rdkafka should try to flush messages before throwing exception. Messages without proper
        flushing will be lost. It is recommended to keep this number high i.e. 10000.
        EOT;
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && $value >= 10;
    }

    public function cast($validatedValue): int
    {
        return (int) $validatedValue;
    }

    public static function getDefaultValue(): int
    {
        return 10000;
    }
}
