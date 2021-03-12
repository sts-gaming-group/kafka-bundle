<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client;

use Sts\KafkaBundle\Client\Contract\ConsumerMessageInterface;

class ConsumerMessage implements ConsumerMessageInterface
{
    private string $topicName;
    private int $partition;
    private string $originalPayload;
    private string $key;
    private int $offset;
    private $decodedPayload;

    public function __construct(
        string $topicName,
        int $partition,
        string $originalPayload,
        string $key,
        int $offset,
        $decodedPayload
    ) {
        $this->topicName = $topicName;
        $this->partition = $partition;
        $this->originalPayload = $originalPayload;
        $this->key = $key;
        $this->offset = $offset;
        $this->decodedPayload = $decodedPayload;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function getPartition(): int
    {
        return $this->partition;
    }

    public function getPayload(): string
    {
        return $this->originalPayload;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return mixed
     */
    public function getDecodedPayload()
    {
        return $this->decodedPayload;
    }
}
