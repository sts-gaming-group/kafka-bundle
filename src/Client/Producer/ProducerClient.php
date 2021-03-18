<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use RdKafka\Producer;
use Sts\KafkaBundle\Client\Traits\CheckProducerTopic;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\Type\ProducerPartition;
use Sts\KafkaBundle\Configuration\Type\ProducerTopic;
use Sts\KafkaBundle\RdKafka\KafkaConfigurationFactory;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ProducerClient
{
    use CheckForRdKafkaExtensionTrait;
    use CheckProducerTopic;

    private int $maxFlushRetries = 10;
    private int $flushTimeoutMs = 10000;
    private int $pollingBatch = 25000;
    private int $pollingTimeoutMs = 0;
    private array $callbacks = [];
    private array $rdKafkaProducers;

    private ?Producer $lastCalledProducer = null;

    private ProducerProvider $producerProvider;
    private KafkaConfigurationFactory $kafkaConfigurationFactory;
    private ConfigurationResolver $configurationResolver;

    public function __construct(
        ProducerProvider $producerProvider,
        KafkaConfigurationFactory $kafkaConfigurationFactory,
        ConfigurationResolver $configurationResolver
    ) {
        $this->producerProvider = $producerProvider;
        $this->kafkaConfigurationFactory = $kafkaConfigurationFactory;
        $this->configurationResolver = $configurationResolver;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function produce($data): self
    {
        $this->isKafkaExtensionLoaded();

        $producer = $this->producerProvider->provide($data);

        $rdKafkaConfig = $this->kafkaConfigurationFactory->create($producer);
        foreach ($this->callbacks as $name => $callback) {
            $rdKafkaConfig->{$name}($callback);
        }

        $producerClass = get_class($producer);
        if (!isset($this->rdKafkaProducers[$producerClass])) {
            $this->rdKafkaProducers[$producerClass] = new Producer($rdKafkaConfig);
        }

        $this->lastCalledProducer = $this->rdKafkaProducers[$producerClass];
        $configuration = $this->configurationResolver->resolve($producer);
        $topic = $this->lastCalledProducer->newTopic($configuration->getValue(ProducerTopic::NAME));

        $message = $producer->produce($data);
        $topic->produce(
            $configuration->getValue(ProducerPartition::NAME),
            0,
            $message->getPayload(),
            $message->getKey()
        );

        if ($this->lastCalledProducer->getOutQLen() % $this->pollingBatch === 0) {
            while ($this->lastCalledProducer->getOutQLen() > 0) {
                $this->lastCalledProducer->poll($this->pollingTimeoutMs);
            }
        }

        return $this;
    }

    public function setCallbacks(array $callbacks): self
    {
        $this->callbacks = $callbacks;

        return $this;
    }

    public function setMaxFlushRetries(int $maxFlushRetries): self
    {
        $this->maxFlushRetries = $maxFlushRetries;

        return $this;
    }

    public function setFlushTimeoutMs(int $flushTimeoutMs): self
    {
        $this->flushTimeoutMs = $flushTimeoutMs;

        return $this;
    }

    public function setPollingBatch(int $pollingBatch): self
    {
        $this->pollingBatch = $pollingBatch;

        return $this;
    }

    public function setPollingTimeoutMs(int $pollingTimeoutMs): self
    {
        $this->pollingTimeoutMs = $pollingTimeoutMs;

        return $this;
    }

    public function flush(): void
    {
        if (!$this->lastCalledProducer) {
            throw new \RuntimeException('You have to call `produce` method first to be able to flush.');
        }

        $result = RD_KAFKA_RESP_ERR_NO_ERROR;
        for ($flushRetries = 0; $flushRetries < $this->maxFlushRetries; $flushRetries++) {
            $result = $this->lastCalledProducer->flush($this->flushTimeoutMs);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            throw new \RuntimeException('Unable to flush, messages might be lost.');
        }
    }
}
