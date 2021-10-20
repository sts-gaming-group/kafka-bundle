<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Event;

class PreMessageConsumedEvent extends AbstractMessageConsumedEvent
{
    private const NAME = 'sts_gaming_group_kafka.pre_message_consumed';

    public static function getEventName(string $consumerName): string
    {
        return self::NAME . '_' . $consumerName;
    }
}
