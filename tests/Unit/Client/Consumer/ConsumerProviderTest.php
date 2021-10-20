<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Client\Consumer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use StsGamingGroup\KafkaBundle\Client\Consumer\ConsumerProvider;
use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;
use StsGamingGroup\KafkaBundle\Client\Consumer\Exception\InvalidConsumerException;

class ConsumerProviderTest extends TestCase
{
    private MockObject $consumerOne;
    private MockObject $consumerTwo;
    private ConsumerProvider $consumerProvider;

    protected function setUp(): void
    {
        $this->consumerOne = $this->createMock(ConsumerInterface::class);
        $this->consumerTwo = $this->createMock(ConsumerInterface::class);
        $this->consumerProvider = new ConsumerProvider();
    }

    public function testAddAndProviderConsumer(): void
    {
        $this->consumerOne->method('getName')
            ->willReturn('consumer_1');
        $this->consumerTwo->method('getName')
            ->willReturn('consumer_2');

        $this->consumerProvider->addConsumer($this->consumerOne)
            ->addConsumer($this->consumerTwo);

        $this->assertInstanceOf(get_class($this->consumerTwo), $this->consumerProvider->provide('consumer_2'));
    }

    public function testMultipleConsumersFound(): void
    {
        $this->consumerOne->method('getName')
            ->willReturn('consumer_2');
        $this->consumerTwo->method('getName')
            ->willReturn('consumer_2');

        $this->consumerProvider->addConsumer($this->consumerOne)
            ->addConsumer($this->consumerTwo);

        $this->expectException(InvalidConsumerException::class);
        $this->expectErrorMessageMatches('/Multiple consumers/');
        $this->consumerProvider->provide('consumer_2');
    }

    public function testNoConsumerFound(): void
    {
        $this->consumerOne->method('getName')
            ->willReturn('consumer_1');
        $this->consumerTwo->method('getName')
            ->willReturn('consumer_2');

        $this->consumerProvider->addConsumer($this->consumerOne)
            ->addConsumer($this->consumerTwo);

        $this->expectException(InvalidConsumerException::class);
        $this->expectErrorMessageMatches('/no matching consumer/');
        $this->consumerProvider->provide('consumer_3');
    }
}
