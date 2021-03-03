<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Consumer;

use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Exception\ConsumerProviderException;

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
            throw new ConsumerProviderException(sprintf('Multiple consumers found with name %s', $name));
        }

        if (!$consumers) {
            throw new ConsumerProviderException(sprintf('There is no matching consumer with name %s.', $name));
        }

        return $consumers[0];
    }
}
