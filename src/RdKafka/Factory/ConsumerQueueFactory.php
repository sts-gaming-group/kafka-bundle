<?php

namespace Sts\KafkaBundle\RdKafka\Factory;

use RdKafka\Consumer as RdKafkaConsumer;
use RdKafka\Queue;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\Brokers;
use Sts\KafkaBundle\Configuration\Type\Offset;
use Sts\KafkaBundle\Configuration\Type\Partition;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\RdKafka\Context;

class ConsumerQueueFactory
{
    private GlobalConfigurationFactory $globalConfigurationFactory;
    private TopicConfigurationFactory $topicConfigurationFactory;

    public function __construct(
        GlobalConfigurationFactory $globalConfigurationFactory,
        TopicConfigurationFactory $topicConfigurationFactory
    ) {
        $this->globalConfigurationFactory = $globalConfigurationFactory;
        $this->topicConfigurationFactory = $topicConfigurationFactory;
    }

    public function create(ResolvedConfiguration $resolvedConfiguration, Context $context): Queue
    {
        $conf = $this->globalConfigurationFactory->create($resolvedConfiguration);
        $rdKafkaConsumer = new RdKafkaConsumer($conf);

        $queue = $rdKafkaConsumer->newQueue();

        foreach ($resolvedConfiguration->getConfigurationValue(Topics::NAME) as $topicName) {
            $rdKafkaTopicConf = $this->topicConfigurationFactory->create($resolvedConfiguration);
            $rdKafkaConsumerTopic = $rdKafkaConsumer->newTopic($topicName, $rdKafkaTopicConf);
            $rdKafkaConsumerTopic->consumeQueueStart(
                $resolvedConfiguration->getConfigurationValue(Partition::NAME),
                $resolvedConfiguration->getConfigurationValue(Offset::NAME),
                $queue
            );
            $context->addKafkaConsumerTopic($rdKafkaConsumerTopic);
        }

        return $queue;
    }
}
