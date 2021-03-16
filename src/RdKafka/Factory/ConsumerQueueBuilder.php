<?php

namespace Sts\KafkaBundle\RdKafka\Factory;

use RdKafka\Consumer as RdKafkaConsumer;
use RdKafka\ConsumerTopic;
use RdKafka\Queue;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\Brokers;
use Sts\KafkaBundle\Configuration\Type\Offset;
use Sts\KafkaBundle\Configuration\Type\Partition;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\RdKafka\Context;

class ConsumerQueueBuilder
{
    private GlobalConfigurationFactory $globalConfigurationFactory;
    private TopicConfigurationFactory $topicConfigurationFactory;
    /**
     * @var array<ConsumerTopic>
     */
    private array $topics;
    private Queue $queue;

    public function __construct(
        GlobalConfigurationFactory $globalConfigurationFactory,
        TopicConfigurationFactory $topicConfigurationFactory
    ) {
        $this->globalConfigurationFactory = $globalConfigurationFactory;
        $this->topicConfigurationFactory = $topicConfigurationFactory;
    }

    public function build(ResolvedConfiguration $resolvedConfiguration): self
    {
        $conf = $this->globalConfigurationFactory->create($resolvedConfiguration);
        $rdKafkaConsumer = new RdKafkaConsumer($conf);

        $this->queue = $rdKafkaConsumer->newQueue();
        foreach ($resolvedConfiguration->getConfigurationValue(Topics::NAME) as $topicName) {
            $rdKafkaTopicConf = $this->topicConfigurationFactory->create($resolvedConfiguration);
            $rdKafkaConsumerTopic = $rdKafkaConsumer->newTopic($topicName, $rdKafkaTopicConf);
            $rdKafkaConsumerTopic->consumeQueueStart(
                $resolvedConfiguration->getConfigurationValue(Partition::NAME),
                $resolvedConfiguration->getConfigurationValue(Offset::NAME),
                $this->queue
            );
            $this->topics[] = $rdKafkaConsumerTopic;
        }

        return $this;
    }

    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }
}
