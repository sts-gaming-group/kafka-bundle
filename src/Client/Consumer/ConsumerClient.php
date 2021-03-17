<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Consumer;

use RdKafka\KafkaConsumer as RdKafkaConsumer;
use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\MaxRetries;
use Sts\KafkaBundle\Configuration\Type\MaxRetryDelay;
use Sts\KafkaBundle\Configuration\Type\RetryDelay;
use Sts\KafkaBundle\Configuration\Type\RetryMultiplier;
use Sts\KafkaBundle\Configuration\Type\Timeout;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\Exception\KafkaException;
use Sts\KafkaBundle\Exception\NullMessageException;
use Sts\KafkaBundle\Exception\NullPayloadException;
use Sts\KafkaBundle\Exception\RecoverableMessageException;
use Sts\KafkaBundle\Exception\TimedOutException;
use Sts\KafkaBundle\Exception\UnrecoverableMessageException;
use Sts\KafkaBundle\Factory\MessageFactory;
use Sts\KafkaBundle\RdKafka\Context;
use Sts\KafkaBundle\RdKafka\Factory\GlobalConfigurationFactory;
use Sts\KafkaBundle\RdKafka\NullRdKafkaMessage;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ConsumerClient
{
    use CheckForRdKafkaExtensionTrait;

    private GlobalConfigurationFactory $globalConfigurationFactory;
    private MessageFactory $messageFactory;
    private ConfigurationResolver $configurationResolver;

    public function __construct(
        GlobalConfigurationFactory $globalConfigurationFactory,
        MessageFactory $messageFactory,
        ConfigurationResolver $configurationResolver
    ) {
        $this->globalConfigurationFactory = $globalConfigurationFactory;
        $this->messageFactory = $messageFactory;
        $this->configurationResolver = $configurationResolver;
    }

    public function consume(ConsumerInterface $consumer): bool
    {
        $this->isKafkaExtensionLoaded();

        $configuration = $this->configurationResolver->resolve($consumer);

        $timeout = $configuration->getConfigurationValue(Timeout::NAME);
        $maxRetries = $configuration->getConfigurationValue(MaxRetries::NAME);
        $retryDelay = $configuration->getConfigurationValue(RetryDelay::NAME);
        $maxRetryDelay = $configuration->getConfigurationValue(MaxRetryDelay::NAME);
        $retryMultiplier = $configuration->getConfigurationValue(RetryMultiplier::NAME);
        $topics = $configuration->getConfigurationValue(Topics::NAME);

        $rdKafkaConfig = $this->globalConfigurationFactory->create($consumer);
        $rdKafkaConsumer = new RdKafkaConsumer($rdKafkaConfig);
        $rdKafkaConsumer->subscribe($topics);

        while (true) {
            $context = $this->createContext($configuration, 0, $rdKafkaConsumer);
            try {
                $rdKafkaMessage = $rdKafkaConsumer->consume($timeout);
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
                $message = $this->messageFactory->create($rdKafkaMessage, $configuration);
            } catch (\Throwable $throwable) {
                $consumer->handleException(new KafkaException($throwable), $rdKafkaMessage, $context);

                continue;
            }

            for ($retry = 0; $retry <= $maxRetries; ++$retry) {
                $context = $this->createContext($configuration, $retry, $rdKafkaConsumer, $rdKafkaMessage);
                $failed = false;
                try {
                    $consumer->consume($message, $context);
                } catch (\Throwable $throwable) {
                    $failed = true;
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
                }

                if (!$failed) {
                    break;
                }
            }

            $retryDelay = $configuration->getConfigurationValue(RetryDelay::NAME);
        }
    }

    private function validateMessage(?RdKafkaMessage $message): void
    {
        if (null === $message || RD_KAFKA_RESP_ERR__PARTITION_EOF === $message->err) {
            throw new NullMessageException('Currently, there are no more messages.');
        }

        if (RD_KAFKA_RESP_ERR__TIMED_OUT === $message->err) {
            throw new TimedOutException(
                'Kafka brokers have timed out or there are no messages. Unable to differentiate the reason.'
            );
        }

        if (null === $message->payload) {
            throw new NullPayloadException('Null payload received in kafka message.');
        }
    }

    private function createContext(
        ResolvedConfiguration $configuration,
        int $retryNo,
        RdKafkaConsumer $rdKafkaConsumer,
        ?RdKafkaMessage $rdKafkaMessage = null
    ): Context {
        return new Context(
            $configuration,
            $retryNo,
            $rdKafkaConsumer,
            $rdKafkaMessage
        );
    }
}
