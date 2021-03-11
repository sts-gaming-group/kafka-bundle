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

    public function __construct(ResolvedConfiguration $resolvedConfiguration)
    {
        $this->resolvedConfiguration = $resolvedConfiguration;
    }

    public function addKafkaConsumerTopic(RdKafkaConsumerTopic $rdKafkaConsumerTopic): self
    {
        $this->rdKafkaConsumerTopics[$rdKafkaConsumerTopic->getName()] = $rdKafkaConsumerTopic;

        return $this;
    }

    public function getResolvedConfiguration(): ResolvedConfiguration
    {
        return $this->resolvedConfiguration;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getResolvedConfigurationValue(string $name)
    {
        return $this->resolvedConfiguration->getConfigurationValue($name);
    }

    public function getRdKafkaConsumerTopicByName(string $name): RdKafkaConsumerTopic
    {
        return $this->rdKafkaConsumerTopics[$name];
    }
}
