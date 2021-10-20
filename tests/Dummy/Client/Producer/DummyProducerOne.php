<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Producer;

use StsGamingGroup\KafkaBundle\Client\Contract\ProducerInterface;
use StsGamingGroup\KafkaBundle\Client\Producer\Message;

class DummyProducerOne implements ProducerInterface
{
    public function produce($data): Message
    {
        return new Message('{"result": true}', '1');
    }

    public function supports($data): bool
    {
        return true;
    }
}
