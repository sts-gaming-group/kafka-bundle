<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\RdKafka;

use RdKafka\ConsumerTopic as RdKafkaConsumerTopic;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

class Context
{
    private ResolvedConfiguration $resolvedConfiguration;
    /**
     * @var array<RdKafkaConsumerTopic>
     */
    private array $rdKafkaConsumerTopics;

    private int $retryNo;

    public function __construct(
        ResolvedConfiguration $resolvedConfiguration,
        array $rdKafkaConsumerTopics,
        int $retryNo
    ) {
        $this->resolvedConfiguration = $resolvedConfiguration;
        foreach ($rdKafkaConsumerTopics as $rdKafkaConsumerTopic) {
            $this->rdKafkaConsumerTopics[$rdKafkaConsumerTopic->getName()] = $rdKafkaConsumerTopic;
        }
        $this->retryNo = $retryNo;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getConfigurationValue(string $name)
    {
        return $this->resolvedConfiguration->getConfigurationValue($name);
    }

    public function getRdKafkaConsumerTopicByName(string $name): RdKafkaConsumerTopic
    {
        return $this->rdKafkaConsumerTopics[$name];
    }

    public function getRetryNo(): int
    {
        return $this->retryNo;
    }
}
