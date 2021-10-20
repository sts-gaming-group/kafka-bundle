<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractMessageConsumedEvent extends Event
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

    abstract public static function getEventName(string $consumerName): string;
}
