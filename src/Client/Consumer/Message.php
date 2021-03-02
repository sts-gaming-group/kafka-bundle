<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Consumer;

class Message
{
    private int $error;
    private string $topicName;
    private int $timestamp;
    private int $partition;
    private string $originalPayload;
    private int $length;
    private string $key;
    private int $offset;
    private ?array $headers;
    private array $decodedPayload;

    public function __construct(
        int $error,
        string $topicName,
        int $timestamp,
        int $partition,
        string $originalPayload,
        int $length,
        string $key,
        int $offset,
        ?array $headers,
        array $decodedPayload
    ) {
        $this->error = $error;
        $this->topicName = $topicName;
        $this->timestamp = $timestamp;
        $this->partition = $partition;
        $this->originalPayload = $originalPayload;
        $this->length = $length;
        $this->key = $key;
        $this->offset = $offset;
        $this->headers = $headers;
        $this->decodedPayload = $decodedPayload;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getPartition(): int
    {
        return $this->partition;
    }

    public function getOriginalPayload(): string
    {
        return $this->originalPayload;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    public function getDecodedPayload(): array
    {
        return $this->decodedPayload;
    }
}
