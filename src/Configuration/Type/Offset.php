<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\CastValueInterface;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Offset implements ConfigurationInterface, CastValueInterface
{
    public const NAME = 'offset';

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
            'Offset from which begin consumption in given topic. Defaults to RD_KAFKA_OFFSET_STORED (%s)',
            self::getDefaultValue()
        );
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && $value > 0;
    }

    public function cast($validatedValue): int
    {
        return (int)$validatedValue;
    }

    public static function getDefaultValue(): int
    {
        return defined('RD_KAFKA_OFFSET_STORED') ? RD_KAFKA_OFFSET_STORED : -1000;
    }
}
