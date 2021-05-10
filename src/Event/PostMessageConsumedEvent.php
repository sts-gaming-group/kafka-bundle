<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Event;

class PostMessageConsumedEvent extends AbstractMessageConsumedEvent
{
    private const NAME = 'sts_kafka.post_message_consumed';

    public static function getEventName(string $consumerName): string
    {
        return self::NAME . '_' . $consumerName;
    }
}
