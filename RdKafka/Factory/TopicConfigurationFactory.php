<?php

namespace Sts\KafkaBundle\RdKafka\Factory;

use RdKafka\TopicConf;
use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Configuration\Type\AutoCommitInterval;
use Sts\KafkaBundle\Configuration\Type\AutoOffsetReset;
use Sts\KafkaBundle\Configuration\Type\OffsetStoreMethod;

class TopicConfigurationFactory
{
    public function create(ConfigurationContainer $configuration): TopicConf
    {
        $topicConf = new TopicConf();
        $topicConf->set('auto.commit.interval.ms', $configuration->getConfiguration(AutoCommitInterval::NAME));
        $topicConf->set('offset.store.method', $configuration->getConfiguration(OffsetStoreMethod::NAME));
        $topicConf->set('auto.offset.reset', $configuration->getConfiguration(AutoOffsetReset::NAME));

        return $topicConf;
    }
}
