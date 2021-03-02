<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Decoder\AvroDecoder;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Symfony\Component\Console\Input\InputOption;

class Decoder implements ConfigurationInterface
{
    public const NAME = 'decoder';
    public const DEFAULT_VALUE = AvroDecoder::class;

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
            'Which decoder to use. Currently only %s available. You can also create custom Decoder by implementing %s',
            self::DEFAULT_VALUE,
            DecoderInterface::class
        );
    }

    public function isValueValid($value): bool
    {
        $classImplements = class_implements($value);
        if (!$classImplements) {
            return false;
        }

        return class_exists($value) && in_array(DecoderInterface::class, $classImplements, true);
    }
}
