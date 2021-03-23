<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use Sts\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Sts\KafkaBundle\Configuration\Traits\BooleanConfigurationTrait;

class RegisterMissingSchemas implements ConsumerConfigurationInterface
{
    use BooleanConfigurationTrait;

    public const NAME = RecordSerializer::OPTION_REGISTER_MISSING_SCHEMAS;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDescription(): string
    {
        return sprintf(
            <<<EOT
        If you want to auto-register missing schemas set this to true. Defaults to %s.
        Refer to flix-tech/avro-serde-php composer package for more information.
        EOT,
            self::getDefaultValue()
        );
    }

    public static function getDefaultValue(): string
    {
        return 'false';
    }
}
