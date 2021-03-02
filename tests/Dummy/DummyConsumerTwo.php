<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy;

use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Consumer\Message;
use Sts\KafkaBundle\RdKafka\Context;

class DummyConsumerTwo implements ConsumerInterface
{
    public const NAME = 'dummy_consumer_two';

    public function consume(Message $message, Context $context): bool
    {
        return false;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
