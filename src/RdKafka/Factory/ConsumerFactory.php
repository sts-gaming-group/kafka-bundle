<?php

namespace Sts\KafkaBundle\RdKafka\Factory;

use RdKafka\Conf;
use RdKafka\Consumer;
use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Configuration\Type\Brokers;
use Sts\KafkaBundle\Configuration\Type\GroupId;
use Sts\KafkaBundle\Configuration\Type\OffsetStoreMethod;

class ConsumerFactory
{
    public function create(ConfigurationContainer $configuration): Consumer
    {
        if (!extension_loaded('rdkafka')) {
            throw new \RuntimeException('rdkafka extension missing in PHP');
        }

        $conf = new Conf();
        $conf->set('log_level', LOG_ERR);
        $conf->set('group.id', $configuration->getConfiguration(GroupId::NAME));
        $conf->set('offset.store.method', $configuration->getConfiguration(OffsetStoreMethod::NAME));

        $consumer = new Consumer($conf);
        $consumer->addBrokers(implode(',', $configuration->getConfiguration(Brokers::NAME)));

        return $consumer;
    }
}
