<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class ProducerTimeoutMs implements ConfigurationInterface
{
    public const NAME = 'producer_timeout_ms';

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
        return 'Timeout for Rdkafka flush.';
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && $value >= 25;
    }

    public static function getDefaultValue(): int
    {
        return 100;
    }
}
