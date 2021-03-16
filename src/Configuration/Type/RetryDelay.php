<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class RetryDelay implements ConfigurationInterface
{
    public const NAME = 'retry_delay';

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
            'Delay (in ms) before message is consumed again after fail. Defaults to %s',
            self::getDefaultValue()
        );
    }

    public function isValueValid($value): bool
    {
        return is_int($value) && $value >= 0;
    }

    public static function getDefaultValue(): int
    {
        return 200;
    }
}
