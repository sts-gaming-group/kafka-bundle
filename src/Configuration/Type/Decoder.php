<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Sts\KafkaBundle\Decoder\AvroDecoder;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Sts\KafkaBundle\Decoder\JsonDecoder;
use Sts\KafkaBundle\Decoder\PlainDecoder;
use Symfony\Component\Console\Input\InputOption;

class Decoder implements ConsumerConfigurationInterface
{
    public const NAME = 'decoder';

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
            'Which decoder to use. Currently available %s. 
            You can also create custom Decoder by implementing %s.
            Default decoder %s',
            implode(', ', [AvroDecoder::class, JsonDecoder::class, PlainDecoder::class]),
            DecoderInterface::class,
            self::getDefaultValue()
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

    public static function getDefaultValue(): string
    {
        return AvroDecoder::class;
    }
}
