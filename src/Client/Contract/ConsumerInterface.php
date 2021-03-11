<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

use Sts\KafkaBundle\RdKafka\Context;

interface ConsumerInterface extends ClientInterface
{
    public function consume(MessageInterface $message, Context $context): bool;
    public function getName(): string;
}
