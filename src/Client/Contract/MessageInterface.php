<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

interface MessageInterface
{
    public function getPayload(): string;

    /**
     * @return mixed
     */
    public function getDecodedPayload();
    public function getKey(): string;
    public function getTopicName(): string;
    public function getPartition(): int;
    public function getOffset(): int;
}
