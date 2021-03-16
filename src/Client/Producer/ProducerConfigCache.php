<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\Producer as RdKafkaProducer;
use RdKafka\ProducerTopic;
use Sts\KafkaBundle\Client\Contract\ProducerInterface;
use Sts\KafkaBundle\Client\Traits\CheckProducerTopic;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\Partition;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\RdKafka\Factory\GlobalConfigurationFactory;

class ProducerConfigCache
{
    use CheckProducerTopic;

    private GlobalConfigurationFactory $globalConfigurationFactory;
    private ConfigurationResolver $configurationResolver;

    /**
     * @var array<ResolvedConfiguration>
     */
    private array $resolvedConfigurations = [];

    /**
     * @var array<Conf>
     */
    private array $rdKafkaConfigurations = [];

    /**
     * @var array<RdKafkaProducer>
     */
    private array $rdKafkaProducers = [];

    /**
     * @var array<ProducerTopic>
     */
    private array $producerTopics = [];

    public function __construct(
        GlobalConfigurationFactory $globalConfigurationFactory,
        ConfigurationResolver $configurationResolver
    ) {
        $this->globalConfigurationFactory = $globalConfigurationFactory;
        $this->configurationResolver = $configurationResolver;
    }

    public function build(ProducerInterface $producer, callable $resultCallback = null): self
    {
        $class = get_class($producer);
        $this->buildResolvedConfiguration($class)
            ->buildRdKafkaConfiguration($class, $resultCallback)
            ->buildRdKafkaProducer($class)
            ->buildProducerTopics($class);

        return $this;
    }

    /**
     * @param ProducerInterface $producer
     * @return array<ProducerTopic>
     */
    public function getTopics(ProducerInterface $producer): array
    {
        return $this->producerTopics[get_class($producer)];
    }

    public function getRdKafkaProducer(ProducerInterface $producer): Producer
    {
        return $this->rdKafkaProducers[get_class($producer)];
    }

    public function getPartition(ProducerInterface $producer): int
    {
        return $this->resolvedConfigurations[get_class($producer)]->getConfigurationValue(Partition::NAME);
    }

    private function buildProducerTopics(string $class): self
    {
        $configuration = $this->resolvedConfigurations[$class];
        $rdKafkaProducer = $this->rdKafkaProducers[$class];

        if (!array_key_exists($class, $this->producerTopics)) {
            foreach ($configuration->getConfigurationValue(Topics::NAME) as $topic) {
                $this->isTopicBlacklisted($topic);
                $this->producerTopics[$class][] = $rdKafkaProducer->newTopic($topic);
            }
        }

        return $this;
    }

    private function buildResolvedConfiguration(string $class): self
    {
        if (!array_key_exists($class, $this->resolvedConfigurations)) {
            $this->resolvedConfigurations[$class] = $this->configurationResolver->resolve($class);
        }

        return $this;
    }

    private function buildRdKafkaConfiguration(string $class, callable $resultCallback = null): self
    {
        if (!array_key_exists($class, $this->rdKafkaConfigurations)) {
            $this->rdKafkaConfigurations[$class] = $this->globalConfigurationFactory->create(
                $this->resolvedConfigurations[$class]
            );
            if ($resultCallback) {
                $this->rdKafkaConfigurations[$class]->setDrMsgCb($resultCallback);
            }
        }

        return $this;
    }

    private function buildRdKafkaProducer(string $class): self
    {
        if (!array_key_exists($class, $this->rdKafkaProducers)) {
            $this->rdKafkaProducers[$class] = new RdKafkaProducer(
                $this->rdKafkaConfigurations[$class]
            );
        }

        return $this;
    }
}
