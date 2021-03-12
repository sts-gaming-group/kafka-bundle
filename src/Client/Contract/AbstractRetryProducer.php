<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

use Sts\KafkaBundle\Client\ProducerMessage;
use Sts\KafkaBundle\RdKafka\Context;

abstract class AbstractRetryProducer implements ProducerInterface
{
    protected ?ConsumerMessageInterface $failedMessage;
    protected ?Context $context;

    public function __construct(ConsumerMessageInterface $failedMessage = null, Context $context = null)
    {
        $this->failedMessage = $failedMessage;
        $this->context = $context;
    }

    abstract public function getName(): string;

    public function getMessage(): ProducerMessageInterface
    {
        return new ProducerMessage('todo', 'todo');
    }
}
