<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class AutoOffsetReset implements ConfigurationInterface
{
    public const NAME = 'auto_offset_reset';
    private const SMALLEST = 'smallest';
    private const LARGEST = 'largest';

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
            'Action to take when there is no initial offset in offset store or the desired offset is out of range: 
        %s - automatically reset the offset to the smallest offset, 
        %s - automatically reset the offset to the largest offset',
            self::SMALLEST,
            self::LARGEST
        );
    }

    public function getDefaultValue(): string
    {
        return self::SMALLEST;
    }
}
