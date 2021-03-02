<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy;

use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Consumer\Message;
use Sts\KafkaBundle\RdKafka\Context;

class DummyConsumerOne implements ConsumerInterface
{
    public const NAME = 'dummy_consumer_one';

    public function consume(Message $message, Context $context): bool
    {
        return true;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
