<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Sts\KafkaBundle\Configuration\Contract\KafkaConfigurationInterface;
use Sts\KafkaBundle\Configuration\Traits\BooleanConfigurationTrait;

class EnableAutoCommit implements KafkaConfigurationInterface, ConsumerConfigurationInterface
{
    use BooleanConfigurationTrait;

    public const NAME = 'enable_auto_commit';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getKafkaProperty(): string
    {
        return 'enable.auto.commit';
    }

    public function getDescription(): string
    {
        return <<<EOT
        If true, periodically commit offset of the last message handed to the application. 
        This committed offset will be used when the process restarts to pick up where it left off. 
        If false, the application will have to call rd_kafka_offset_store() to store an offset (optional).
        Defaults to true. Must be passed as a string `true` or `false`
        EOT;
    }

    public static function getDefaultValue(): string
    {
        return 'true';
    }
}
