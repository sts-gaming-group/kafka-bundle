<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Producer;

use RdKafka\Producer;
use StsGamingGroup\KafkaBundle\Client\Contract\PartitionAwareProducerInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ProducerInterface;
use StsGamingGroup\KafkaBundle\Configuration\ConfigurationResolver;
use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use StsGamingGroup\KafkaBundle\Configuration\Type\ProducerPartition;
use StsGamingGroup\KafkaBundle\Configuration\Type\ProducerTopic;
use StsGamingGroup\KafkaBundle\RdKafka\Factory\KafkaConfigurationFactory;
use StsGamingGroup\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ProducerClient
{
    use CheckForRdKafkaExtensionTrait;

    private int $maxFlushRetries = 10;
    private int $flushTimeoutMs = 10000;
    private int $pollingBatch = 25000;
    private int $pollingTimeoutMs = 0;
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

        $producerClass = get_class($producer);
        if (!isset($this->rdKafkaProducers[$producerClass])) {
            $this->rdKafkaProducers[$producerClass] = new Producer($rdKafkaConfig);
        }

        $this->lastCalledProducer = $this->rdKafkaProducers[$producerClass];
        $configuration = $this->configurationResolver->resolve($producer);
        $topic = $this->lastCalledProducer->newTopic($configuration->getValue(ProducerTopic::NAME));

        $message = $producer->produce($data);
        $topic->produce(
            $this->getPartition($data, $producer, $configuration),
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

    /**
     * @param mixed $data
     */
    private function getPartition($data, ProducerInterface $producer, ResolvedConfiguration $configuration): int
    {
        if (!$producer instanceof PartitionAwareProducerInterface) {
            return $configuration->getValue(ProducerPartition::NAME);
        }

        return $producer->getPartition($data, $configuration);
    }
}
