<?php

namespace Sts\KafkaBundle\RdKafka\Factory;

use RdKafka\Consumer as RdKafkaConsumer;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\Brokers;

class ConsumerFactory
{
    private GlobalConfigurationFactory $globalConfigurationFactory;

    public function __construct(GlobalConfigurationFactory $globalConfigurationFactory)
    {
        $this->globalConfigurationFactory = $globalConfigurationFactory;
    }

    public function create(ResolvedConfiguration $resolvedConfiguration): RdKafkaConsumer
    {
        $conf = $this->globalConfigurationFactory->create($resolvedConfiguration);
        $consumer = new RdKafkaConsumer($conf);
        $consumer->addBrokers(implode(',', $resolvedConfiguration->getConfigurationValue(Brokers::NAME)));

        return $consumer;
    }
}
