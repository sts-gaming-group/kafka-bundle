<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy\Client\Consumer;

use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\RdKafka\Context;

class DummyConsumerThree extends DummyConsumerTwo
{
    public const NAME = 'dummy_consumer_three';

    public function consume(Message $message, Context $context): bool
    {
        return true;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function handleException(\Exception $exception, Context $context): bool
    {
        return false;
    }
}
