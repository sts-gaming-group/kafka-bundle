<?php

namespace Sts\KafkaBundle\RdKafka\Factory;

use RdKafka\TopicConf;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

class TopicConfigurationFactory
{
    public function create(ResolvedConfiguration $resolvedConfiguration): TopicConf
    {
        $topicConf = new TopicConf();

        foreach ($resolvedConfiguration->getTopicConfigurations() as $topicConfiguration) {
            $topicConf->set(
                $topicConfiguration['configuration']->getKafkaProperty(),
                $topicConfiguration['resolvedValue']
            );
        }

        return $topicConf;
    }
}
