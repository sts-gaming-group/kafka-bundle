<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

use Sts\KafkaBundle\Client\Producer\Message;

interface ProducerInterface extends ClientInterface
{
    public function produce($data): Message;
    public function supports($data): bool;
}
