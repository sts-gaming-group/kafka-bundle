<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\RdKafka\Context;

interface RetryProducerInterface extends ProducerInterface
{
    public function getMessage(Message $message, Context $context): string;
}
