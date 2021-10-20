<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Producer;

use StsGamingGroup\KafkaBundle\Client\Contract\ProducerInterface;
use StsGamingGroup\KafkaBundle\Client\Producer\Exception\InvalidProducerException;

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
     * @param mixed $data
     * @return ProducerInterface
     */
    public function provide($data): ProducerInterface
    {
        $producers = [];

        foreach ($this->producers as $producer) {
            if ($producer->supports($data)) {
                $producers[] = $producer;
            }
        }

        if (count($producers) > 1) {
            throw new InvalidProducerException('Multiple producers found');
        }

        if (!$producers) {
            throw new InvalidProducerException('There is no matching producer.');
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
