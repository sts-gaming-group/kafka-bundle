<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class AutoCommitInterval implements ConfigurationInterface
{
    public const NAME = 'auto_commit_interval';

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
        return 'The frequency in ms that the consumer offsets are committed (written) to offset storage. (0 = disable). Defaults to 1000 ms.';
    }

    public function getDefaultValue(): string
    {
        return '1000';
    }
}
