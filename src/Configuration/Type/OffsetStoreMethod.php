<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\GlobalConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class OffsetStoreMethod implements GlobalConfigurationInterface
{
    public const BROKER = 'broker';
    public const FILE = 'file';
    public const NAME = 'offset_store_method';
    public const DEFAULT_VALUE = self::BROKER;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getKafkaProperty(): string
    {
        return 'offset.store.method';
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Offset commit store methods available: 
            - %1$s - local file store (offset.store.path, et.al), 
            - %2$s - broker commit store (requires Apache Kafka 0.8.2 or later on the broker). Defaults to %2$s',
            self::FILE,
            self::BROKER
        );
    }

    public function isValueValid($value): bool
    {
        return in_array($value, [self::BROKER, self::FILE], true);
    }
}
