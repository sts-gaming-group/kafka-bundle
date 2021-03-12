<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy;

use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Client\Contract\ConsumerMessageInterface;
use Sts\KafkaBundle\RdKafka\Context;

class DummyConsumerOne implements ConsumerInterface
{
    public const NAME = 'dummy_consumer_one';

    public function consume(ConsumerMessageInterface $message, Context $context): bool
    {
        return true;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
