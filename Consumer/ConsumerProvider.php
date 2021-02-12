<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Consumer;

use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Exception\ConsumerProviderException;

class ConsumerProvider
{
    /**
     * @var ConsumerInterface[]
     */
    private array $consumers = [];

    public function __construct(iterable $consumers = [])
    {
        foreach ($consumers as $consumer) {
            $this->addConsumer($consumer);
        }
    }

    public function addConsumer(ConsumerInterface $consumer): void
    {
        $this->consumers[] = $consumer;
    }

    public function provide(string $type = '', string $topic = ''): ConsumerInterface
    {
        if (!$type && !$topic) {
            $this->createException('You need to provide type or topic.');;
        }

        if ($type && $topic) {
            return $this->provideByTypeAndTopic($type, $topic);
        }

        return $type ? $this->provideByType($type) : $this->provideByTopic($topic);
    }

    public function getConsumers(): array
    {
        return $this->consumers;
    }

    private function provideByTypeAndTopic(string $type, string $topic): ConsumerInterface
    {
        $consumers = [];
        foreach ($this->consumers as $consumer) {
            if ($this->supportsType($consumer, $type) && $this->supportsTopic($consumer, $topic)) {
                $consumers[] = $consumer;
            }
        }

        if (!$consumers) {
            $this->createException('There is no matching consumer. Check provided type and topic.');
        }

        if (count($consumers) > 1) {
            $this->createException('Multiple consumers support the same type and topic. Unable to decide which one to choose.');
        }

        return $consumers[0];
    }

    private function provideByType(string $type): ConsumerInterface
    {
        $consumers = [];
        foreach ($this->consumers as $consumer) {
            if ($this->supportsType($consumer, $type)) {
                $consumers[] = $consumer;
            }
        }

        if (!$consumers) {
            $this->createException('There is no matching consumer. Check provided type.');
        }

        if (count($consumers) > 1) {
            $this->createException('Multiple consumers support the same type. Please provide topic to be more specific.');
        }

        return $consumers[0];
    }

    private function provideByTopic(string $topic): ConsumerInterface
    {
        $consumers = [];
        foreach ($this->consumers as $consumer) {
            if ($this->supportsTopic($consumer, $topic)) {
                $consumers[] = $consumer;
            }
        }

        if (!$consumers) {
            $this->createException('There is no matching consumer. Check provided topic.');
        }

        if (count($consumers) > 1) {
            $this->createException('Multiple consumers support the same topic. Please provide consumer type to be more specific.');
        }

        return $consumers[0];
    }

    private function createException(string $msg): void
    {
        throw new ConsumerProviderException($msg);
    }

    private function supportsType(ConsumerInterface $consumer, string $type): bool
    {
        return $consumer->getSupportedType() === $type;
    }

    private function supportsTopic(ConsumerInterface $consumer, string $topic): bool
    {
        return in_array($topic, $consumer->getSupportedTopics(), true);
    }
}
