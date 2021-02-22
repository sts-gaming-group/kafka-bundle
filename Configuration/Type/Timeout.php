<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Timeout implements ConfigurationInterface
{
    public const NAME = 'timeout';

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
        return 'Consumer timeout. Defaults to 1000 ms.';
    }

    public function getDefaultValue(): int
    {
        return 1000;
    }
}
