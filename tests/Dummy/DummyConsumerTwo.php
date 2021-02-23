<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy;

use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Consumer\Message;

class DummyConsumerTwo implements ConsumerInterface
{
    public const NAME = 'dummy_consumer_two';

    public function consume(ConfigurationContainer $configuration, Message $message): bool
    {
        return false;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
