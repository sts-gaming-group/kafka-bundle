<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class RetryMultiplier implements ConsumerConfigurationInterface
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
            $this->getDefaultValue()
        );
    }

    public function isValueValid($value): bool
    {
        return is_int($value) && $value > 0;
    }

    public function getDefaultValue(): int
    {
        return 2;
    }
}
