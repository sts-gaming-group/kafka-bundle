<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Contract;

use StsGamingGroup\KafkaBundle\Client\Producer\Message;

interface ProducerInterface extends ClientInterface
{
    /**
     * @param mixed $data
     * @return Message
     */
    public function produce($data): Message;

    /**
     * @param mixed $data
     * @return bool
     */
    public function supports($data): bool;
}
