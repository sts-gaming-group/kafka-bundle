<?php

namespace Sts\KafkaBundle\RdKafka\Factory;

use RdKafka\Consumer as RdKafkaConsumer;
use RdKafka\ConsumerTopic as RdKafkaConsumerTopic;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\RdKafka\Context;

class ContextFactory
{
    /**
     * @var array<RdKafkaConsumerTopic>
     */
    private array $rdKafkaConsumerTopics;

    public function create(RdKafkaConsumer $rdKafkaConsumer, ResolvedConfiguration $resolvedConfiguration): Context
    {
        return new Context($rdKafkaConsumer, $resolvedConfiguration, $this->rdKafkaConsumerTopics);
    }

    public function addTopic(RdKafkaConsumerTopic $rdKafkaConsumerTopic): self
    {
        $this->rdKafkaConsumerTopics[] = $rdKafkaConsumerTopic;

        return $this;
    }
}
