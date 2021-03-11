<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\CastValueInterface;
use Sts\KafkaBundle\Configuration\Contract\GlobalConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class LogLevel implements GlobalConfigurationInterface, CastValueInterface
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
        return sprintf('Logging level (syslog(3) levels). Defaults to LOG_ERR (%s)', self::getDefaultValue());
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value);
    }

    public function cast($validatedValue): int
    {
        return (int) $validatedValue;
    }

    public static function getDefaultValue(): int
    {
        return LOG_ERR;
    }
}
