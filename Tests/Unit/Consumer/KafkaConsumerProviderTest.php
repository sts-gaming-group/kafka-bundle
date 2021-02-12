<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Consumer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Consumer\ConsumerProvider;
use Sts\KafkaBundle\Exception\ConsumerProviderException;

class ConsumerProviderTest extends TestCase
{
    private MockObject $consumerOne;
    private MockObject $consumerTwo;
    private MockObject $consumerThree;
    private ConsumerProvider $ConsumerProvider;

    protected function setUp(): void
    {
        $this->consumerOne = $this->createMock(ConsumerInterface::class);
        $this->consumerTwo = $this->createMock(ConsumerInterface::class);
        $this->consumerThree = $this->createMock(ConsumerInterface::class);
        $this->ConsumerProvider = new ConsumerProvider($this->getConsumers());
    }

    public function testAddConsumer(): void
    {
        $this->assertCount(3, $this->ConsumerProvider->getConsumers());
        $this->ConsumerProvider->addConsumer($this->createMock(ConsumerInterface::class));
        $this->assertCount(4, $this->ConsumerProvider->getConsumers());
    }

    public function testNoTypeOrTopicException(): void
    {
        $this->expectException(ConsumerProviderException::class);
        $this->ConsumerProvider->provide();
    }

    public function testProvideByTypeAndTopic(): void
    {
        $this->consumerOne->expects($this->exactly(2))
            ->method('getSupportedType')
            ->willReturn('type_1');

        $this->consumerOne->expects($this->exactly(2))
            ->method('getSupportedTopics')
            ->willReturn(['topic_1', 'topic_2']);

        $this->consumerThree->expects($this->once())
            ->method('getSupportedType')
            ->willReturn('type_1');

        $this->consumerThree->expects($this->once())
            ->method('getSupportedTopics')
            ->willReturn(['topic_3', 'topic_4']);

        $consumer = $this->ConsumerProvider->provide('type_1', 'topic_2');

        $this->assertEquals('type_1', $consumer->getSupportedType());
        foreach ($consumer->getSupportedTopics() as $topic) {
            $this->assertThat(
                $topic,
                $this->logicalOr(
                    $this->equalTo('topic_1'),
                    $this->equalTo('topic_2')
                )
            );
        }
    }

    public function testProvideByTypeAndTopicNoConsumersFound(): void
    {
        $this->consumerOne->expects($this->once())
            ->method('getSupportedType')
            ->willReturn('type_1');

        $this->consumerOne->expects($this->never())
            ->method('getSupportedTopics');

        $this->consumerThree->expects($this->once())
            ->method('getSupportedType')
            ->willReturn('type_1');

        $this->consumerThree->expects($this->never())
            ->method('getSupportedTopics');

        $this->expectException(ConsumerProviderException::class);
        $this->expectExceptionMessageMatches('/no matching consumer/');
        $this->ConsumerProvider->provide('type_3', 'topic_5');

    }

    public function testProvideByTypeAndTopicMultipleConsumersFound(): void
    {
        $this->consumerOne->expects($this->once())
            ->method('getSupportedType')
            ->willReturn('type_1');

        $this->consumerOne->expects($this->once())
            ->method('getSupportedTopics')
            ->willReturn(['topic_1', 'topic_2']);

        $this->consumerTwo->expects($this->once())
            ->method('getSupportedType')
            ->willReturn('type_1');

        $this->consumerTwo->expects($this->once())
            ->method('getSupportedTopics')
            ->willReturn(['topic_1', 'topic_2']);

        $this->expectException(ConsumerProviderException::class);
        $this->expectExceptionMessageMatches('/Multiple consumers/');
        $this->ConsumerProvider->provide('type_1', 'topic_1');
    }

    public function testProvideByType(): void
    {
        $this->consumerTwo->expects($this->once())
            ->method('getSupportedType')
            ->willReturn('type_1');

        $this->consumerTwo->expects($this->never())
            ->method('getSupportedTopics');

        $this->consumerThree->expects($this->exactly(2))
            ->method('getSupportedType')
            ->willReturn('type_2');

        $this->consumerThree->expects($this->once())
            ->method('getSupportedTopics')
            ->willReturn(['topic_2']);

        $consumer = $this->ConsumerProvider->provide('type_2', '');
        $this->assertEquals('type_2', $consumer->getSupportedType());
        $this->assertEquals(['topic_2'], $consumer->getSupportedTopics());
    }

    public function testProvideByTopic(): void
    {
        $this->consumerTwo->expects($this->never())
            ->method('getSupportedType');

        $this->consumerTwo->expects($this->once())
            ->method('getSupportedTopics')
            ->willReturn(['topic_1']);

        $this->consumerThree->expects($this->once())
            ->method('getSupportedType')
            ->willReturn('type_2');

        $this->consumerThree->expects($this->exactly(2))
            ->method('getSupportedTopics')
            ->willReturn(['topic_2']);

        $consumer = $this->ConsumerProvider->provide('', 'topic_2');
        $this->assertEquals('type_2', $consumer->getSupportedType());
        $this->assertEquals(['topic_2'], $consumer->getSupportedTopics());
    }

    private function getConsumers(): iterable
    {
        foreach ([$this->consumerOne, $this->consumerTwo, $this->consumerThree] as $consumer) {
            yield $consumer;
        }
    }
}
