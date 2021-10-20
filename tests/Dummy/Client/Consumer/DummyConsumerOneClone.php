<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Consumer;

use StsGamingGroup\KafkaBundle\Client\Consumer\Message;
use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;
use StsGamingGroup\KafkaBundle\RdKafka\Context;

class DummyConsumerOneClone implements ConsumerInterface
{
    public const NAME = 'dummy_consumer_one';

    public function consume(Message $message, Context $context): bool
    {
        return false;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function handleException(\Exception $exception, Context $context): bool
    {
        return true;
    }
}
