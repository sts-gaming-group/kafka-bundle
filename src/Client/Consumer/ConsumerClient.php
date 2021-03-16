<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Consumer;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\MaxRetries;
use Sts\KafkaBundle\Configuration\Type\MaxRetryDelay;
use Sts\KafkaBundle\Configuration\Type\RetryDelay;
use Sts\KafkaBundle\Configuration\Type\RetryMultiplier;
use Sts\KafkaBundle\Configuration\Type\Timeout;
use Sts\KafkaBundle\Exception\KafkaException;
use Sts\KafkaBundle\Exception\NullMessageException;
use Sts\KafkaBundle\Exception\NullPayloadException;
use Sts\KafkaBundle\Exception\RecoverableMessageException;
use Sts\KafkaBundle\Exception\UnrecoverableMessageException;
use Sts\KafkaBundle\Factory\MessageFactory;
use Sts\KafkaBundle\RdKafka\Context;
use Sts\KafkaBundle\RdKafka\Factory\ConsumerQueueBuilder;
use Sts\KafkaBundle\RdKafka\NullRdKafkaMessage;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ConsumerClient
{
    use CheckForRdKafkaExtensionTrait;

    private ConsumerQueueBuilder $consumerQueueBuilder;
    private MessageFactory $messageFactory;

    public function __construct(ConsumerQueueBuilder $consumerQueueBuilder, MessageFactory $messageFactory)
    {
        $this->consumerQueueBuilder = $consumerQueueBuilder;
        $this->messageFactory = $messageFactory;
    }

    public function consume(ConsumerInterface $consumer, ResolvedConfiguration $resolvedConfiguration): bool
    {
        $this->isKafkaExtensionLoaded();

        $timeout = $resolvedConfiguration->getConfigurationValue(Timeout::NAME);
        $maxRetries = $resolvedConfiguration->getConfigurationValue(MaxRetries::NAME);
        $retryDelay = $resolvedConfiguration->getConfigurationValue(RetryDelay::NAME);
        $maxRetryDelay = $resolvedConfiguration->getConfigurationValue(MaxRetryDelay::NAME);
        $retryMultiplier = $resolvedConfiguration->getConfigurationValue(RetryMultiplier::NAME);

        $this->consumerQueueBuilder->build($resolvedConfiguration);
        $queue = $this->consumerQueueBuilder->getQueue();

        while (true) {
            $context = $this->createContext($resolvedConfiguration, 0);
            try {
                $rdKafkaMessage = $queue->consume($timeout);
            } catch (\Throwable $throwable) {
                $consumer->handleException(new KafkaException($throwable), new NullRdKafkaMessage(), $context);

                continue;
            }

            try {
                $this->validateMessage($rdKafkaMessage);
            } catch (\Throwable $throwable) {
                $message = $rdKafkaMessage ?? new NullRdKafkaMessage();
                $consumer->handleException(new KafkaException($throwable), $message, $context);

                continue;
            }

            try {
                $message = $this->messageFactory->create($rdKafkaMessage, $resolvedConfiguration);
            } catch (\Throwable $throwable) {
                $consumer->handleException(new KafkaException($throwable), $rdKafkaMessage, $context);

                continue;
            }

            for ($retry = 0; $retry <= $maxRetries; ++$retry) {
                $context = $this->createContext($resolvedConfiguration, $retry);
                try {
                    $consumer->consume($message, $context);
                } catch (\Throwable $throwable) {
                    $consumer->handleException(new KafkaException($throwable), $rdKafkaMessage, $context);

                    if ($throwable instanceof UnrecoverableMessageException) {
                        break;
                    }

                    if ($throwable instanceof RecoverableMessageException) {
                        ++$maxRetries;
                    }

                    if ($retry !== $maxRetries) {
                        usleep($retryDelay * 1000);
                        $retryDelay *= $retryMultiplier;
                        if ($retryDelay > $maxRetryDelay) {
                            $retryDelay = $maxRetryDelay;
                        }
                    }

                    continue;
                }

                break;
            }

            $retryDelay = $resolvedConfiguration->getConfigurationValue(RetryDelay::NAME);
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

    private function createContext(ResolvedConfiguration $resolvedConfiguration, int $retryNo): Context
    {
        return new Context($resolvedConfiguration, $this->consumerQueueBuilder->getTopics(), $retryNo);
    }
}
