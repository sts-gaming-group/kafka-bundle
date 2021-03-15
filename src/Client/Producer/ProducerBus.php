<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use Sts\KafkaBundle\Configuration\Type\ProducerPartition;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ProducerBus
{
    use CheckForRdKafkaExtensionTrait;

    public static int $pollingBatch = 10000;

    private ProducerHandlerProvider $producerHandlerProvider;
    private ProducerBusCache $producerBusCache;

    public function __construct(ProducerHandlerProvider $producerHandlerProvider, ProducerBusCache $producerBusCache)
    {
        $this->producerHandlerProvider = $producerHandlerProvider;
        $this->producerBusCache = $producerBusCache;
    }

    public function dispatch($message): void
    {
        $this->isKafkaExtensionLoaded();

        if (!\is_object($message)) {
            throw new \TypeError('You must pass an object to dispatch method.');
        }

        $handler = $this->producerHandlerProvider->provide($message);
        $handlerClass = get_class($handler);

        $resolvedConfiguration = $this->producerBusCache->getResolvedConfiguration($handlerClass);
        $conf = $this->producerBusCache->getRdKafkaConfiguration($handlerClass, $resolvedConfiguration);
        $rdKafkaProducer = $this->producerBusCache->getRdKafkaProducer($handlerClass, $conf);
        $topics = $this->producerBusCache->getProducerTopics(
            $handlerClass,
            $resolvedConfiguration,
            $rdKafkaProducer
        );

        $handlerMessage = $handler->produce($message);
        foreach ($topics as $topic) {
            $topic->produce(
                $resolvedConfiguration->getConfigurationValue(ProducerPartition::NAME),
                0,
                $handlerMessage->getPayload(),
                $handlerMessage->getKey()
            );
        }

        if ($rdKafkaProducer->getOutQLen() % static::$pollingBatch === 0) {
            while ($rdKafkaProducer->getOutQLen() > 0) {
                $rdKafkaProducer->poll(0);
            }
        }
    }

    public function poll(): void
    {
        foreach ($this->producerBusCache->getRdKafkaProducers() as $rdKafkaProducer) {
            while ($rdKafkaProducer->getOutQLen() > 0) {
                $rdKafkaProducer->poll(0);
            }
        }
    }
}
