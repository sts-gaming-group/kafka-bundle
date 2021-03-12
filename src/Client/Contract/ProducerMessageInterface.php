<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

interface ProducerMessageInterface
{
    public function getPayload(): string;
    public function getKey(): string;
    public function supportedBy(string $producerClass): bool;
}
