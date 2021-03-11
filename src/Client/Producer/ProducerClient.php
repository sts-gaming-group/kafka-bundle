<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use RdKafka\Producer as RdKafkaProducer;
use Sts\KafkaBundle\Client\Contract\ProducerInterface;
use Sts\KafkaBundle\Client\Traits\CheckProducerTopic;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\Type\ProducerPartition;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\RdKafka\Factory\GlobalConfigurationFactory;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ProducerClient
{
    use CheckForRdKafkaExtensionTrait;
    use CheckProducerTopic;
private $howmany = 0;
    private ConfigurationResolver $configurationResolver;
    private GlobalConfigurationFactory $globalConfigurationFactory;

    public function __construct(
        ConfigurationResolver $configurationResolver,
        GlobalConfigurationFactory $globalConfigurationFactory
    ) {
        $this->configurationResolver = $configurationResolver;
        $this->globalConfigurationFactory = $globalConfigurationFactory;
    }

    public function produce(ProducerInterface $producer): bool
    {
        $this->isKafkaExtensionLoaded();

        $resolvedConfiguration = $this->configurationResolver->resolve($producer);
        $conf = $this->globalConfigurationFactory->create($resolvedConfiguration);
        $rdKafkaProducer = new RdKafkaProducer($conf);

        $topics = [];
        foreach ($resolvedConfiguration->getConfigurationValue(Topics::NAME) as $topic) {
            $this->isTopicBlacklisted($topic);
            $topics[] = $rdKafkaProducer->newTopic($topic);
        }

        $message = $producer->getMessage();
        foreach ($topics as $topic) {
            $topic->produce(
                $resolvedConfiguration->getConfigurationValue(ProducerPartition::NAME),
                0,
                $message->getPayload(),
                $message->getKey()
            );
        }

        for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
            $result = $rdKafkaProducer->flush(50);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                $this->howmany++;
                break;
            }
        }
        dump($this->howmany);
        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {

            throw new \RuntimeException('Was unable to flush, messages might be lost!');
        }
//        die('end');

        return true;
    }
}
