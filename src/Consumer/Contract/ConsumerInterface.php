<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Consumer\Contract;

use Sts\KafkaBundle\Consumer\Message;
use Sts\KafkaBundle\RdKafka\Context;

interface ConsumerInterface
{
    public function consume(Message $message, Context $context): bool;
    public function getName(): string;
}
