<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

use Sts\KafkaBundle\RdKafka\Context;

abstract class AbstractRetryProducer implements ProducerInterface
{
    protected ?MessageInterface $failedMessage;
    protected ?Context $context;

    public function __construct(MessageInterface $failedMessage = null, Context $context = null)
    {
        $this->failedMessage = $failedMessage;
        $this->context = $context;
    }

    abstract public function getName(): string;

    public function getMessage(): MessageInterface
    {
        return $this->failedMessage;
    }
}
