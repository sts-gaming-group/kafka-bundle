<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\RdKafka;

use RdKafka\Consumer as RdKafkaConsumer;
use RdKafka\ConsumerTopic as RdKafkaConsumerTopic;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

class Context
{
    private RdKafkaConsumer $rdKafkaConsumer;
    private ResolvedConfiguration $resolvedConfiguration;
    /**
     * @var array<RdKafkaConsumerTopic>
     */
    private array $rdKafkaConsumerTopics;

    public function __construct(
        RdKafkaConsumer $rdKafkaConsumer,
        ResolvedConfiguration $resolvedConfiguration,
        array $rdKafkaConsumerTopics
    ) {
        $this->rdKafkaConsumer = $rdKafkaConsumer;
        $this->resolvedConfiguration = $resolvedConfiguration;
        foreach ($rdKafkaConsumerTopics as $rdKafkaConsumerTopic) {
            $this->rdKafkaConsumerTopics[$rdKafkaConsumerTopic->getName()] = $rdKafkaConsumerTopic;
        }
    }

    public function getRdKafkaConsumer(): RdKafkaConsumer
    {
        return $this->rdKafkaConsumer;
    }

    public function getResolvedConfiguration(): ResolvedConfiguration
    {
        return $this->resolvedConfiguration;
    }

    /**
     * @return array<RdKafkaConsumerTopic>
     */
    public function getRdKafkaConsumerTopics(): array
    {
        return $this->rdKafkaConsumerTopics;
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
}
