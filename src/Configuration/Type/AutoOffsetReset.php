<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\TopicConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class AutoOffsetReset implements TopicConfigurationInterface
{
    public const NAME = 'auto_offset_reset';
    public const SMALLEST = 'smallest';
    public const LARGEST = 'largest';
    public const DEFAULT_VALUE = self::SMALLEST;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getKafkaProperty(): string
    {
        return 'auto.offset.reset';
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getDescription(): string
    {
        return sprintf(
            'What to do when there is no initial offset in Kafka or if the current offset does not exist any more on the server (e.g. because that data has been deleted)
                    Available options: 
                    %s - automatically reset the offset to the smallest offset, 
                    %s - automatically reset the offset to the largest offset',
            self::SMALLEST,
            self::LARGEST
        );
    }

    public function isValueValid($value): bool
    {
        return in_array($value, [self::SMALLEST, self::LARGEST], true);
    }
}
