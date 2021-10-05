<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy\Client\Producer;

use Sts\KafkaBundle\Client\Contract\ProducerInterface;
use Sts\KafkaBundle\Client\Producer\Message;

class DummyProducerTwo implements ProducerInterface
{
    public function produce($data): Message
    {
        return new Message('{"result": false}', '1');
    }

    public function supports($data): bool
    {
        return true;
    }
}
