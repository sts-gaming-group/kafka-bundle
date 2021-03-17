<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\RdKafka;

use RdKafka\KafkaConsumer as RdKafkaConsumer;
use RdKafka\ConsumerTopic as RdKafkaConsumerTopic;
use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

class Context
{
    private ResolvedConfiguration $resolvedConfiguration;
    private int $retryNo;
    private RdKafkaConsumer $rdKafkaConsumer;
    private ?RdKafkaMessage $rdKafkaMessage;

    public function __construct(
        ResolvedConfiguration $resolvedConfiguration,
        int $retryNo,
        RdKafkaConsumer $rdKafkaConsumer,
        ?RdKafkaMessage $rdKafkaMessage = null
    ) {
        $this->resolvedConfiguration = $resolvedConfiguration;
        $this->retryNo = $retryNo;
        $this->rdKafkaConsumer = $rdKafkaConsumer;
        $this->rdKafkaMessage = $rdKafkaMessage;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getConfigurationValue(string $name)
    {
        return $this->resolvedConfiguration->getConfigurationValue($name);
    }

    public function getRetryNo(): int
    {
        return $this->retryNo;
    }

    public function getRdKafkaConsumer(): RdKafkaConsumer
    {
        return $this->rdKafkaConsumer;
    }

    public function getRdKafkaMessage(): ?RdKafkaMessage
    {
        return $this->rdKafkaMessage;
    }
}
