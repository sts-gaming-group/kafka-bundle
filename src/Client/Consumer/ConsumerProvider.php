<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Consumer;

use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;
use StsGamingGroup\KafkaBundle\Client\Consumer\Exception\InvalidConsumerException;

class ConsumerProvider
{
    /**
     * @var array<ConsumerInterface>
     */
    protected array $consumers = [];

    public function addConsumer(ConsumerInterface $consumer): self
    {
        $this->consumers[] = $consumer;

        return $this;
    }

    /**
     * @param string $name
     * @return ConsumerInterface
     */
    public function provide(string $name): ConsumerInterface
    {
        $consumers = [];

        foreach ($this->consumers as $consumer) {
            if ($consumer->getName() === $name) {
                $consumers[] = $consumer;
            }
        }

        if (count($consumers) > 1) {
            throw new InvalidConsumerException(sprintf('Multiple consumers found with name %s', $name));
        }

        if (!$consumers) {
            throw new InvalidConsumerException(sprintf('There is no matching consumer with name %s.', $name));
        }

        return $consumers[0];
    }

    /**
     * @return array<ConsumerInterface>
     */
    public function getAll(): array
    {
        return $this->consumers;
    }
}
