<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Event;

class PostMessageConsumedEvent extends AbstractMessageConsumedEvent
{
    public const EVENT_PREFIX = 'sts_kafka.post_message_consumed_';

    public static function getEventName(string $consumerName): string
    {
        return self::EVENT_PREFIX . $consumerName;
    }
}
