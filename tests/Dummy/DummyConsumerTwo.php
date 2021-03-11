<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy;

use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Client\Contract\MessageInterface;
use Sts\KafkaBundle\RdKafka\Context;

class DummyConsumerTwo implements ConsumerInterface
{
    public const NAME = 'dummy_consumer_two';

    public function consume(MessageInterface $message, Context $context): bool
    {
        return false;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
