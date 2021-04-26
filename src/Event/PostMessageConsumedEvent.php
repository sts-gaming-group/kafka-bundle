<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PostMessageConsumedEvent extends Event
{
    private int $consumedMessages;
    private float $consumptionTimeMs;

    public function __construct(int $consumedMessages, float $consumptionTimeMs)
    {
        $this->consumedMessages = $consumedMessages;
        $this->consumptionTimeMs = $consumptionTimeMs;
    }

    public function getConsumedMessages(): int
    {
        return $this->consumedMessages;
    }

    public function getConsumptionTimeMs(): float
    {
        return $this->consumptionTimeMs;
    }

    public static function getEventName(string $consumerName): string
    {
        return 'sts_kafka.post_message_consumed_event_' . $consumerName;
    }
}
