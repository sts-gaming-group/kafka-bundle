<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Consumer;

use RdKafka\KafkaConsumer as RdKafkaConsumer;
use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\EnableAutoCommit;
use Sts\KafkaBundle\Configuration\Type\MaxRetries;
use Sts\KafkaBundle\Configuration\Type\MaxRetryDelay;
use Sts\KafkaBundle\Configuration\Type\RetryDelay;
use Sts\KafkaBundle\Configuration\Type\RetryMultiplier;
use Sts\KafkaBundle\Configuration\Type\Timeout;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\Exception\KafkaException;
use Sts\KafkaBundle\Exception\NullMessageException;
use Sts\KafkaBundle\Exception\RecoverableMessageException;
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
        $enableAutoCommit = $configuration->getValue(EnableAutoCommit::NAME);

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
            } catch (NullMessageException $exception) {
                $consumer->handleException(
                    new KafkaException($exception),
                    $this->createContext($configuration, $rdKafkaConsumer, $rdKafkaMessage)
                );

                continue;
            }

            for ($retry = 0; $retry <= $maxRetries; ++$retry) {
                $context = $this->createContext($configuration, $rdKafkaConsumer, $rdKafkaMessage, $retry);
                try {
                    $message = $this->messageFactory->create($rdKafkaMessage, $configuration);
                    $consumer->consume($message, $context);
                } catch (ValidationException | RecoverableMessageException $exception) {
                    $consumer->handleException(new KafkaException($exception), $context);

                    if ($exception instanceof ValidationException) {
                        if ($enableAutoCommit === 'false') {
                            $rdKafkaConsumer->commit($rdKafkaMessage);
                        }

                        break;
                    }

                    if ($exception instanceof RecoverableMessageException) {
                        if ($retry !== $maxRetries) {
                            usleep($retryDelay * 1000);
                            $retryDelay *= $retryMultiplier;
                            if ($retryDelay > $maxRetryDelay) {
                                $retryDelay = $maxRetryDelay;
                            }
                        }

                        continue;
                    }
                }

                break;
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
