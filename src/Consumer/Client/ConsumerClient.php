<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Consumer\Client;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\Offset;
use Sts\KafkaBundle\Configuration\Type\Partition;
use Sts\KafkaBundle\Configuration\Type\Timeout;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Consumer\Contract\KafkaExceptionAwareConsumerInterface;
use Sts\KafkaBundle\Exception\KafkaException;
use Sts\KafkaBundle\Exception\NullMessageException;
use Sts\KafkaBundle\Exception\NullPayloadException;
use Sts\KafkaBundle\Factory\MessageFactory;
use Sts\KafkaBundle\RdKafka\Context;
use Sts\KafkaBundle\RdKafka\Factory\ConsumerFactory;
use Sts\KafkaBundle\RdKafka\Factory\ContextFactory;
use Sts\KafkaBundle\RdKafka\Factory\TopicConfigurationFactory;
use Sts\KafkaBundle\RdKafka\NullRdKafkaMessage;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ConsumerClient
{
    use CheckForRdKafkaExtensionTrait;

    private ConsumerFactory $consumerFactory;
    private TopicConfigurationFactory $topicConfigurationFactory;
    private ContextFactory $kafkaContextFactory;
    private MessageFactory $messageFactory;

    public function __construct(
        ConsumerFactory $consumerFactory,
        TopicConfigurationFactory $topicConfigurationFactory,
        ContextFactory $kafkaContextFactory,
        MessageFactory $messageFactory
    ) {
        $this->consumerFactory = $consumerFactory;
        $this->topicConfigurationFactory = $topicConfigurationFactory;
        $this->kafkaContextFactory = $kafkaContextFactory;
        $this->messageFactory = $messageFactory;
    }

    public function consume(ConsumerInterface $consumer, ResolvedConfiguration $resolvedConfiguration): bool
    {
        $this->checkForRdKafka();

        $rdKafkaConsumer = $this->consumerFactory->create($resolvedConfiguration);
        $queue = $rdKafkaConsumer->newQueue();

        foreach ($resolvedConfiguration->getConfigurationValue(Topics::NAME) as $topicName) {
            $rdKafkaTopicConf = $this->topicConfigurationFactory->create($resolvedConfiguration);
            $rdKafkaConsumerTopic = $rdKafkaConsumer->newTopic($topicName, $rdKafkaTopicConf);
            $rdKafkaConsumerTopic->consumeQueueStart(
                $resolvedConfiguration->getConfigurationValue(Partition::NAME),
                $resolvedConfiguration->getConfigurationValue(Offset::NAME),
                $queue
            );
            $this->kafkaContextFactory->addTopic($rdKafkaConsumerTopic);
        }

        $context = $this->kafkaContextFactory->create($rdKafkaConsumer, $resolvedConfiguration);
        while (true) {
            try {
                $rdKafkaMessage = $queue->consume($resolvedConfiguration->getConfigurationValue(Timeout::NAME));
            } catch (\Exception $exception) {
                if ($consumer instanceof KafkaExceptionAwareConsumerInterface) {
                    $consumer->handleException(
                        new KafkaException($exception, new NullRdKafkaMessage(), $context)
                    );
                }

                continue;
            }

            try {
                $this->validateMessage($rdKafkaMessage, $context);
            } catch (KafkaException $exception) {
                if ($consumer instanceof KafkaExceptionAwareConsumerInterface) {
                    $consumer->handleException($exception);
                }

                continue;
            }

            $consumer->consume(
                $this->messageFactory->create($rdKafkaMessage, $resolvedConfiguration),
                $context
            );
        }
    }

    private function validateMessage(?RdKafkaMessage $message, Context $context): void
    {
        if (null === $message) {
            throw new NullMessageException(
                new \Exception('Null message received from kafka.'),
                new NullRdKafkaMessage(),
                $context
            );
        }

        if (null === $message->payload) {
            throw new NullPayloadException(
                new \Exception('Null payload received from kafka.'),
                $message,
                $context
            );
        }

        if ($message->err) {
            throw new KafkaException(
                new \Exception(sprintf('Received error with code %s from Kafka', $message->err)),
                new NullRdKafkaMessage(),
                $context
            );
        }
    }
}
