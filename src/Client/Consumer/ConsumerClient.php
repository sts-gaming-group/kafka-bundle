<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Consumer;

use RdKafka\KafkaConsumer as RdKafkaConsumer;
use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\EnableAutoOffsetStore;
use Sts\KafkaBundle\Configuration\Type\MaxRetries;
use Sts\KafkaBundle\Configuration\Type\MaxRetryDelay;
use Sts\KafkaBundle\Configuration\Type\RetryDelay;
use Sts\KafkaBundle\Configuration\Type\RetryMultiplier;
use Sts\KafkaBundle\Configuration\Type\Timeout;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\Exception\KafkaException;
use Sts\KafkaBundle\Exception\NullMessageException;
use Sts\KafkaBundle\Exception\RecoverableMessageException;
use Sts\KafkaBundle\Exception\UnrecoverableMessageException;
use Sts\KafkaBundle\Exception\ValidationException;
use Sts\KafkaBundle\Factory\MessageFactory;
use Sts\KafkaBundle\RdKafka\Context;
use Sts\KafkaBundle\RdKafka\KafkaConfigurationFactory;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ConsumerClient
{
    use CheckForRdKafkaExtensionTrait;

    private KafkaConfigurationFactory $kafkaConfigurationFactory;
    private MessageFactory $messageFactory;
    private ConfigurationResolver $configurationResolver;

    public function __construct(
        KafkaConfigurationFactory $kafkaConfigurationFactory,
        MessageFactory $messageFactory,
        ConfigurationResolver $configurationResolver
    ) {
        $this->kafkaConfigurationFactory = $kafkaConfigurationFactory;
        $this->messageFactory = $messageFactory;
        $this->configurationResolver = $configurationResolver;
    }

    public function consume(ConsumerInterface $consumer): bool
    {
        $this->isKafkaExtensionLoaded();

        $configuration = $this->configurationResolver->resolve($consumer);

        $timeout = $configuration->getValue(Timeout::NAME);
        $maxRetries = $configuration->getValue(MaxRetries::NAME);
        $retryDelay = $configuration->getValue(RetryDelay::NAME);
        $maxRetryDelay = $configuration->getValue(MaxRetryDelay::NAME);
        $retryMultiplier = $configuration->getValue(RetryMultiplier::NAME);
        $topics = $configuration->getValue(Topics::NAME);
        $enableAutoOffsetStore = $configuration->getValue(EnableAutoOffsetStore::NAME);

        $rdKafkaConfig = $this->kafkaConfigurationFactory->create($consumer);
        $rdKafkaConsumer = new RdKafkaConsumer($rdKafkaConfig);
        $rdKafkaConsumer->subscribe($topics);

        while (true) {
            try {
                $rdKafkaMessage = $rdKafkaConsumer->consume($timeout);
            } catch (\Throwable $throwable) {
                $consumer->handleException(
                    new KafkaException($throwable),
                    $this->createContext($configuration, $rdKafkaConsumer)
                );

                continue;
            }

            try {
                $this->validateRdKafkaMessage($rdKafkaMessage);
            } catch (\Throwable $throwable) {
                $consumer->handleException(
                    new KafkaException($throwable),
                    $this->createContext($configuration, $rdKafkaConsumer, $rdKafkaMessage)
                );

                continue;
            }

            try {
                $message = $this->messageFactory->create($rdKafkaMessage, $configuration);
            } catch (\Throwable $throwable) {
                if ($throwable instanceof ValidationException && $enableAutoOffsetStore === 'false') {
                    $rdKafkaConsumer->commit($rdKafkaMessage);
                }
                $consumer->handleException(
                    new KafkaException($throwable),
                    $this->createContext($configuration, $rdKafkaConsumer, $rdKafkaMessage)
                );

                continue;
            }

            for ($retry = 0; $retry <= $maxRetries; ++$retry) {
                $context = $this->createContext($configuration, $rdKafkaConsumer, $rdKafkaMessage, $retry);
                $failed = false;
                try {
                    $consumer->consume($message, $context);
                } catch (\Throwable $throwable) {
                    $failed = true;
                    $consumer->handleException(new KafkaException($throwable), $context);

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

            $retryDelay = $configuration->getValue(RetryDelay::NAME);
        }
    }

    private function validateRdKafkaMessage(?RdKafkaMessage $message): void
    {
        if (null === $message || RD_KAFKA_RESP_ERR__PARTITION_EOF === $message->err) {
            throw new NullMessageException('Currently, there are no more messages.');
        }

        if (RD_KAFKA_RESP_ERR__TIMED_OUT === $message->err) {
            throw new NullMessageException(
                'Kafka brokers have timed out or there are no messages. Unable to differentiate the reason.'
            );
        }

        if (null === $message->payload) {
            throw new NullMessageException('Null payload received in kafka message.');
        }
    }

    private function createContext(
        ResolvedConfiguration $configuration,
        RdKafkaConsumer $rdKafkaConsumer,
        ?RdKafkaMessage $rdKafkaMessage = null,
        int $retryNo = 0
    ): Context {
        return new Context(
            $configuration,
            $retryNo,
            $rdKafkaConsumer,
            $rdKafkaMessage
        );
    }
}
