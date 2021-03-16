<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class RetryMultiplier implements ConfigurationInterface
{
    public const NAME = 'retry_multiplier';

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
            'Causes the retry delay to be higher before each retry. Defaults to %s',
            self::getDefaultValue()
        );
    }

    public function isValueValid($value): bool
    {
        return is_int($value) && $value > 0;
    }

    public static function getDefaultValue(): int
    {
        return 2;
    }
}
