<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Topics implements ConfigurationInterface
{
    public const NAME = 'topics';

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
        return 'Consumer topics to read messages from. Defaults to empty array - must be chosen explicitly.';
    }

    public function getDefaultValue(): array
    {
        return [];
    }
}
