<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Consumer;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
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
            } catch (\Throwable $throwable) {
                $consumer->handleException(new KafkaException($throwable), new NullRdKafkaMessage(), $context);

                continue;
            }

            try {
                $this->validateMessage($rdKafkaMessage);
            } catch (\Throwable $exception) {
                $message = $rdKafkaMessage ?? new NullRdKafkaMessage();
                $consumer->handleException(new KafkaException($exception), $message, $context);

                continue;
            }

            try {
                $message = $this->messageFactory->create($rdKafkaMessage, $resolvedConfiguration);
            } catch (\Throwable $exception) {
                $consumer->handleException(new KafkaException($exception), $rdKafkaMessage, $context);

                continue;
            }

            $consumer->consume($message, $context);
        }
    }

    private function validateMessage(?RdKafkaMessage $message): void
    {
        if (null === $message) {
            throw new NullMessageException('Null message received from kafka.');
        }

        if (null === $message->payload) {
            throw new NullPayloadException('Null payload received from kafka.');
        }
    }
}
