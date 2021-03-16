<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use RdKafka\Producer;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ProducerClient
{
    use CheckForRdKafkaExtensionTrait;

    public const MAX_FLUSH_RETRIES = 10;
    public const FLUSH_TIMEOUT_MS = 10000;
    public static int $pollingBatch = 10000;

    private ProducerProvider $producerProvider;
    private ProducerConfigCache $producerConfigCache;
    private Producer $rdKafkaProducer;
    private ?\Closure $deliveryCallback = null;

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

        $this->producerConfigCache->build($producer, $this->deliveryCallback);
        $this->rdKafkaProducer = $this->producerConfigCache->getRdKafkaProducer($producer);
        $topics = $this->producerConfigCache->getTopics($producer);

        $message = $producer->produce($data);
        foreach ($topics as $topic) {
            $topic->produce(
                $this->producerConfigCache->getPartition($producer),
                0,
                $message->getPayload(),
                $message->getKey()
            );
        }

        if ($this->rdKafkaProducer->getOutQLen() % static::$pollingBatch === 0) {
            while ($this->rdKafkaProducer->getOutQLen() > 0) {
                $this->rdKafkaProducer->poll(0);
            }
        }

        return $this;
    }

    public function setDeliveryCallback(callable $deliveryCallback): self
    {
        $this->deliveryCallback = $deliveryCallback;

        return $this;
    }

    public function flush(): void
    {
        if (!$this->rdKafkaProducer) {
            throw new \RuntimeException('You have to call produce method first to flush anything.');
        }

        for ($flushRetries = 0; $flushRetries < self::MAX_FLUSH_RETRIES; $flushRetries++) {
            $result = $this->rdKafkaProducer->flush(self::FLUSH_TIMEOUT_MS);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            throw new \RuntimeException('Unable to flush, messages might be lost.');
        }
    }

    public function __destruct()
    {
        $this->flush();
    }
}
