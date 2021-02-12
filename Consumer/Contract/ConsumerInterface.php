<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Consumer\Contract;

interface ConsumerInterface
{
    public function consume(array $payload): string;
    public function getSupportedType(): string;
    public function getSupportedTopics(): array;
}
