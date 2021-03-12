<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

abstract class AbstractProducer implements ProducerInterface
{
    private ProducerMessageInterface $producerMessage;

    abstract public function getName(): string;

    public function setMessage(ProducerMessageInterface $producerMessage): self
    {
        $this->producerMessage = $producerMessage;

        return $this;
    }

    public function getMessage(): ProducerMessageInterface
    {
        return $this->producerMessage;
    }
}
