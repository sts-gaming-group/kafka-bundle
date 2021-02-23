<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Consumer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Consumer\ConsumerProvider;
use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Exception\ConsumerProviderException;

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

        $this->expectException(ConsumerProviderException::class);
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

        $this->expectException(ConsumerProviderException::class);
        $this->expectErrorMessageMatches('/no matching consumer/');
        $this->consumerProvider->provide('consumer_3');
    }
}
