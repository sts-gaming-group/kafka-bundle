<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Decoder\AvroDecoder;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Symfony\Component\Console\Input\InputOption;

class SchemaRegistry implements ConfigurationInterface
{
    public const NAME = 'schema_registry';

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
        return 'Schema registry url needed for decoding/encoding messages. Defaults to localhost.';
    }

    public function getDefaultValue(): string
    {
        return 'localhost';
    }
}
