<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class DryRun implements ConfigurationInterface
{
    public function getName(): string
    {
        return 'dry_run';
    }

    public function getMode(): int
    {
        return InputOption::VALUE_NONE;
    }

    public function getDescription(): string
    {
        return 'If set, messages will not be forwarded to registered consumers. Defaults to false.';
    }

    public function getDefaultValue(): bool
    {
        return false;
    }
}
