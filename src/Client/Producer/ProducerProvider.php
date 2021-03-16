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
            throw new ProducerProviderException('Multiple producers found');
        }

        if (!$producers) {
            throw new ProducerProviderException('There is no matching producer.');
        }

        return $producers[0];
    }
}
