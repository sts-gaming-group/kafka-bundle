<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use Sts\KafkaBundle\Configuration\Type\ProducerPartition;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ProducerClient
{
    use CheckForRdKafkaExtensionTrait;

    public static int $pollingBatch = 10000;

    private ProducerProvider $producerProvider;
    private ProducerConfigCache $producerConfigCache;

    public function __construct(ProducerProvider $producerProvider, ProducerConfigCache $producerConfigCache)
    {
        $this->producerProvider = $producerProvider;
        $this->producerConfigCache = $producerConfigCache;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function produce($data): self
    {
        $this->isKafkaExtensionLoaded();

        $producer = $this->producerProvider->provide($data);
        $producerClass = get_class($producer);

        $resolvedConfiguration = $this->producerConfigCache->getResolvedConfiguration($producerClass);
        $conf = $this->producerConfigCache->getRdKafkaConfiguration($producerClass, $resolvedConfiguration);
        $rdKafkaProducer = $this->producerConfigCache->getRdKafkaProducer($producerClass, $conf);
        $topics = $this->producerConfigCache->getProducerTopics(
            $producerClass,
            $resolvedConfiguration,
            $rdKafkaProducer
        );

        $message = $producer->produce($data);
        foreach ($topics as $topic) {
            $topic->produce(
                $resolvedConfiguration->getConfigurationValue(ProducerPartition::NAME),
                0,
                $message->getPayload(),
                $message->getKey()
            );
        }

        if ($rdKafkaProducer->getOutQLen() % static::$pollingBatch === 0) {
            while ($rdKafkaProducer->getOutQLen() > 0) {
                $rdKafkaProducer->poll(0);
            }
        }

        return $this;
    }

    public function poll(): void
    {
        foreach ($this->producerConfigCache->getRdKafkaProducers() as $rdKafkaProducer) {
            while ($rdKafkaProducer->getOutQLen() > 0) {
                $rdKafkaProducer->poll(0);
            }
        }
    }

    public function __destruct()
    {
        $this->poll();
    }
}
