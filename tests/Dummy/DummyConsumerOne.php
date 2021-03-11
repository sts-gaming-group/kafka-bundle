<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy;

use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Client\Contract\MessageInterface;
use Sts\KafkaBundle\RdKafka\Context;

class DummyConsumerOne implements ConsumerInterface
{
    public const NAME = 'dummy_consumer_one';

    public function consume(MessageInterface $message, Context $context): bool
    {
        return true;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
