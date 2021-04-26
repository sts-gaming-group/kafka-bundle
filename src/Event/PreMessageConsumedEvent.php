<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Event;

class PreMessageConsumedEvent extends AbstractMessageConsumedEvent
{
    public const EVENT_PREFIX = 'sts_kafka.pre_message_consumed_event_';

    public static function getEventName(string $consumerName): string
    {
        return self::EVENT_PREFIX . $consumerName;
    }
}
