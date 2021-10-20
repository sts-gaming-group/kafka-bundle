<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Traits\ObjectConfigurationTrait;
use StsGamingGroup\KafkaBundle\Decoder\AvroDecoder;
use StsGamingGroup\KafkaBundle\Decoder\Contract\DecoderInterface;
use StsGamingGroup\KafkaBundle\Decoder\JsonDecoder;
use StsGamingGroup\KafkaBundle\Decoder\PlainDecoder;
use Symfony\Component\Console\Input\InputOption;

class Decoder implements ConsumerConfigurationInterface
{
    use ObjectConfigurationTrait;

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
            $this->getDefaultValue()
        );
    }

    public function getDefaultValue(): string
    {
        return AvroDecoder::class;
    }

    protected function getInterface(): string
    {
        return DecoderInterface::class;
    }
}
