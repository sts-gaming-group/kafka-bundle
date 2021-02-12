<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Functional\Command\Mock;

use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;

class MockConsumerOne implements ConsumerInterface
{
    public function consume(array $payload): string
    {
        return 'OK';
    }

    public function getSupportedType(): string
    {
        return 'mock_type_1';
    }

    public function getSupportedTopics(): array
    {
        return ['topic_1', 'topic_2'];
    }
}
