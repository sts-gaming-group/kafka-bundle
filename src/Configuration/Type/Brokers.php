<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Contract\GlobalConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Brokers implements GlobalConfigurationInterface
{
    public const NAME = 'brokers';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY;
    }

    public function getDescription(): string
    {
        return 'Comma-separated list of brokers in the format: broker1,broker2 i.e. 172.0.0.1:9092,127.0.0.2:9092';
    }

    public function isValueValid($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $topic) {
            if (!is_string($topic) || '' === $topic) {
                return false;
            }
        }

        return true;
    }

    public function getKafkaProperty(): string
    {
        return 'metadata.broker.list';
    }

    public static function getDefaultValue(): array
    {
        return ['127.0.0.1', '127.0.0.2'];
    }
}
