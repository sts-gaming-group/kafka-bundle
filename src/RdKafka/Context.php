<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\RdKafka;

use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use RdKafka\KafkaConsumer as RdKafkaConsumer;
use RdKafka\Message as RdKafkaMessage;

class Context
{
    private ResolvedConfiguration $configuration;
    private RdKafkaConsumer $consumer;
    private RdKafkaMessage $message;
    private int $retryNo;

    public function __construct(
        ResolvedConfiguration $configuration,
        RdKafkaConsumer $consumer,
        RdKafkaMessage $message,
        int $retryNo
    ) {
        $this->configuration = $configuration;
        $this->consumer = $consumer;
        $this->message = $message;
        $this->retryNo = $retryNo;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getValue(string $name)
    {
        return $this->configuration->getValue($name);
    }

    public function getRdKafkaConsumer(): RdKafkaConsumer
    {
        return $this->consumer;
    }

    public function getRdKafkaMessage(): RdKafkaMessage
    {
        return $this->message;
    }

    public function getRetryNo(): int
    {
        return $this->retryNo;
    }
}
