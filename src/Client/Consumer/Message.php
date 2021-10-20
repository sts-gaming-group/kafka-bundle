<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Consumer;

class Message
{
    private string $topicName;
    private int $partition;
    private string $payload;
    private int $offset;
    /**
     * @var mixed
     */
    private $data;
    private ?string $key;

    /**
     * @param string $topicName
     * @param int $partition
     * @param string $payload
     * @param int $offset
     * @param mixed $data
     * @param string|null $key
     */
    public function __construct(
        string $topicName,
        int $partition,
        string $payload,
        int $offset,
        $data,
        ?string $key = null
    ) {
        $this->topicName = $topicName;
        $this->partition = $partition;
        $this->payload = $payload;
        $this->offset = $offset;
        $this->data = $data;
        $this->key = $key;
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

    public function getKey(): ?string
    {
        return $this->key;
    }
}
