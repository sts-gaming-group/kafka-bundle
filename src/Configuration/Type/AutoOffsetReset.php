<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Sts\KafkaBundle\Configuration\Contract\KafkaConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class AutoOffsetReset implements ConsumerConfigurationInterface, KafkaConfigurationInterface
{
    public const NAME = 'auto_offset_reset';
    public const SMALLEST = 'smallest';
    public const LARGEST = 'largest';

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
            <<<EOT
        What to do when there is no initial offset in Kafka or if the current offset does not exist any more on the server.
        Available options: 
        %s - automatically reset the offset to the smallest offset, 
        %s - automatically reset the offset to the largest offset
        EOT,
            self::SMALLEST,
            self::LARGEST
        );
    }

    public function isValueValid($value): bool
    {
        return in_array($value, [self::SMALLEST, self::LARGEST], true);
    }

    public static function getDefaultValue(): string
    {
        return self::SMALLEST;
    }
}
