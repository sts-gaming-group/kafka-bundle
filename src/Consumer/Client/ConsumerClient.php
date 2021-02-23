<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Consumer\Client;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Configuration\Type\Offset;
use Sts\KafkaBundle\Configuration\Type\Partition;
use Sts\KafkaBundle\Configuration\Type\Timeout;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Factory\MessageFactory;
use Sts\KafkaBundle\RdKafka\Factory\ConsumerFactory;
use Sts\KafkaBundle\RdKafka\Factory\TopicConfigurationFactory;

class ConsumerClient
{
    private ConsumerFactory $consumerFactory;
    private TopicConfigurationFactory $topicConfigurationFactory;
    private MessageFactory $messageFactory;

    public function __construct(
        ConsumerFactory $consumerFactory,
        TopicConfigurationFactory $topicConfigurationFactory,
        MessageFactory $messageFactory
    ) {
        $this->consumerFactory = $consumerFactory;
        $this->topicConfigurationFactory = $topicConfigurationFactory;
        $this->messageFactory = $messageFactory;
    }

    public function consume(ConsumerInterface $consumer, ConfigurationContainer $configuration): bool
    {
        $consumerClient = $this->consumerFactory->create($configuration);
        $queue = $consumerClient->newQueue();

        foreach ($configuration->getConfiguration(Topics::NAME) as $topic) {
            $topicConf = $this->topicConfigurationFactory->create($configuration);
            $kafkaTopic = $consumerClient->newTopic($topic, $topicConf);
            $kafkaTopic->consumeQueueStart(
                $configuration->getConfiguration(Partition::NAME),
                $configuration->getConfiguration(Offset::NAME),
                $queue
            );
        }

        $timeout = $configuration->getConfiguration(Timeout::NAME);

        while (true) {
            $rdKafkaMessage = $queue->consume($timeout);
            try {
                $isValid = $this->validateMessage($rdKafkaMessage);
            } catch (\Exception $exception) {
                // todo: log exception?
                $isValid = false;
            }

            if ($isValid) {
                $consumer->consume(
                    $configuration,
                    $this->messageFactory->create($rdKafkaMessage, $configuration)
                );
            }
        }
    }

    private function validateMessage(?RdKafkaMessage $message): bool
    {
        if (null === $message) {
            return false;
        }

        if ($message->err === RD_KAFKA_RESP_ERR__PARTITION_EOF) {
            return false;
        }

        if (null === $message->payload) {
            return false;
        }

        if ($message->err) {
            throw new \RuntimeException($message->errstr());
        }

        return true;
    }
}
