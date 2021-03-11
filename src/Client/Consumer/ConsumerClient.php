<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Consumer;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Client\Contract\KafkaExceptionAwareConsumerInterface;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\Timeout;
use Sts\KafkaBundle\Exception\KafkaException;
use Sts\KafkaBundle\Exception\NullMessageException;
use Sts\KafkaBundle\Exception\NullPayloadException;
use Sts\KafkaBundle\Factory\MessageFactory;
use Sts\KafkaBundle\RdKafka\Context;
use Sts\KafkaBundle\RdKafka\Factory\ConsumerQueueFactory;
use Sts\KafkaBundle\RdKafka\NullRdKafkaMessage;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ConsumerClient
{
    use CheckForRdKafkaExtensionTrait;

    private ConsumerQueueFactory $consumerQueueFactory;
    private MessageFactory $messageFactory;

    public function __construct(ConsumerQueueFactory $consumerQueueFactory, MessageFactory $messageFactory)
    {
        $this->consumerQueueFactory = $consumerQueueFactory;
        $this->messageFactory = $messageFactory;
    }

    public function consume(ConsumerInterface $consumer, ResolvedConfiguration $resolvedConfiguration): bool
    {
        $this->isKafkaExtensionLoaded();

        $context = new Context($resolvedConfiguration);
        $queue = $this->consumerQueueFactory->create($resolvedConfiguration, $context);

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
