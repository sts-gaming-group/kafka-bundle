<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Producer\Contract;

interface ProducerInterface
{
    public function getMessage(): string;
}
