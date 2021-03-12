<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client;

use Sts\KafkaBundle\Client\Contract\ProducerMessageInterface;

class ProducerMessage implements ProducerMessageInterface
{
    private string $payload;
    private string $key;

    public function __construct(string $payload, string $key)
    {
        $this->payload = $payload;
        $this->key = $key;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
