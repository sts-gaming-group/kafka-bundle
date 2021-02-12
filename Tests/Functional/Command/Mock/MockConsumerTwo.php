<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Functional\Command\Mock;

use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;

class MockConsumerTwo implements ConsumerInterface
{
    public function consume(array $payload): string
    {
        return 'FAIL';
    }

    public function getSupportedType(): string
    {
        return 'mock_type_2';
    }

    public function getSupportedTopics(): array
    {
        return ['topic_3', 'topic_4'];
    }
}
