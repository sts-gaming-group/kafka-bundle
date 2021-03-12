<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use Sts\KafkaBundle\Client\Contract\ProducerInterface;
use Sts\KafkaBundle\Exception\ProducerProviderException;

class ProducerProvider
{
    /**
     * @var array<ProducerInterface>
     */
    protected array $producers = [];

    public function addProducer(ProducerInterface $producer): self
    {
        $this->producers[] = $producer;

        return $this;
    }

    /**
     * @param string $name
     * @return ProducerInterface
     */
    public function provide(string $name): ProducerInterface
    {
        $producers = [];

        foreach ($this->producers as $producer) {
            if ($producer->getName() === $name) {
                $producers[] = $producer;
            }
        }

        if (count($producers) > 1) {
            throw new ProducerProviderException(sprintf('Multiple producers found with name %s', $name));
        }

        if (!$producers) {
            throw new ProducerProviderException(sprintf('There is no matching producer with name %s.', $name));
        }

        return $producers[0];
    }

    /**
     * @return array<ProducerInterface>
     */
    public function getProducers(): array
    {
        return $this->producers;
    }
}
