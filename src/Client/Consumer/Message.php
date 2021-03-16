<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Consumer;

class Message
{
    private string $topicName;
    private int $partition;
    private string $payload;
    private string $key;
    private int $offset;
    /**
     * @var mixed
     */
    private $data;

    /**
     * @param string $topicName
     * @param int $partition
     * @param string $payload
     * @param string $key
     * @param int $offset
     * @param mixed $data
     */
    public function __construct(
        string $topicName,
        int $partition,
        string $payload,
        string $key,
        int $offset,
        $data
    ) {
        $this->topicName = $topicName;
        $this->partition = $partition;
        $this->payload = $payload;
        $this->key = $key;
        $this->offset = $offset;
        $this->data = $data;
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
        return $this->payload;
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
    public function getData()
    {
        return $this->data;
    }
}
