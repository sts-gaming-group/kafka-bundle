<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Sts\KafkaBundle\Factory\DecoderFactory;
use Sts\KafkaBundle\Factory\MessageFactory;

class MessageFactoryTest extends TestCase
{
    private MockObject $decoder;
    private MockObject $decoderFactory;
    private MockObject $resolvedConfiguration;
    private MessageFactory $messageFactory;

    protected function setUp(): void
    {
        $this->decoder = $this->createMock(DecoderInterface::class);
        $this->decoderFactory = $this->createMock(DecoderFactory::class);
        $this->resolvedConfiguration = $this->createMock(ResolvedConfiguration::class);
        $this->messageFactory = new MessageFactory($this->decoderFactory);
    }

    public function testOneInstanceOfDecoderCreated(): void
    {
        $this->decoderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->decoder);

        $this->decoder->expects($this->exactly(2))
            ->method('decode')
            ->willReturn([]);

        $this->messageFactory->create($this->getRdKafkaMessage(), $this->resolvedConfiguration);
        $this->messageFactory->create($this->getRdKafkaMessage(), $this->resolvedConfiguration);
    }

    public function testMessageCreated(): void
    {
        $this->decoderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->decoder);

        $this->decoder->expects($this->once())
            ->method('decode')
            ->willReturn(['decoded_payload']);

        $message = $this->messageFactory->create($this->getRdKafkaMessage(), $this->resolvedConfiguration);
        $this->assertEquals(0, $message->getError());
        $this->assertEquals('topic', $message->getTopicName());
        $this->assertEquals(123, $message->getTimestamp());
        $this->assertEquals(0, $message->getPartition());
        $this->assertEquals('some_payload', $message->getOriginalPayload());
        $this->assertEquals(123, $message->getLength());
        $this->assertEquals('1', $message->getKey());
        $this->assertEquals(90, $message->getOffset());
        $this->assertEquals([], $message->getHeaders());
        $this->assertEquals(['decoded_payload'], $message->getDecodedPayload());
    }

    private function getRdKafkaMessage(): RdKafkaMessage
    {
        $rdKafkaMessage = new RdKafkaMessage();
        $rdKafkaMessage->err = 0;
        $rdKafkaMessage->topic_name = 'topic';
        $rdKafkaMessage->timestamp = 123;
        $rdKafkaMessage->partition = 0;
        $rdKafkaMessage->payload = 'some_payload';
        $rdKafkaMessage->len = 123;
        $rdKafkaMessage->key = '1';
        $rdKafkaMessage->offset = 90;
        $rdKafkaMessage->headers = [];

        return $rdKafkaMessage;
    }
}
