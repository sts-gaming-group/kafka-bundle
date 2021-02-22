<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Partition implements ConfigurationInterface
{
    public const NAME = 'partition';

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
        return 'Which partition consumer should consume from. Defaults to 0.';
    }

    public function getDefaultValue(): int
    {
        return 0;
    }
}
