<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

use Sts\KafkaBundle\Client\Producer\Message;

interface ProducerHandlerInterface extends ClientInterface
{
    public function produce($message): Message;
    public function supports($messageClass): bool;
}
