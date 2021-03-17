<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class MaxRetryDelay implements ConsumerConfigurationInterface
{
    public const NAME = 'max_retry_delay';

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
        return sprintf('Maximum retry delay in ms. Defaults to %s', self::getDefaultValue());
    }

    public function isValueValid($value): bool
    {
        return is_int($value) && $value >= 0;
    }

    public static function getDefaultValue(): int
    {
        return 2000;
    }
}
