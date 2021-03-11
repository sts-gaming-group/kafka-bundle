<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\GlobalConfigurationInterface;
use Sts\KafkaBundle\Configuration\Traits\BooleanConfigurationTrait;

class EnableAutoOffsetStore implements GlobalConfigurationInterface
{
    use BooleanConfigurationTrait;

    public const NAME = 'enable_auto_offset_store';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getKafkaProperty(): string
    {
        return 'enable.auto.offset.store';
    }

    public function getDescription(): string
    {
        return <<<EOT
        Automatically store offset of last message provided to application. 
        The offset store is an in-memory store of the next offset to (auto-)commit for each partition. 
        Defaults to true. Must be passed as a string `true` or `false`
        EOT;
    }

    public static function getDefaultValue(): bool
    {
        return true;
    }
}
