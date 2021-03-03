<?php

namespace Sts\KafkaBundle\RdKafka\Factory;

use RdKafka\Conf;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

class GlobalConfigurationFactory
{
    public function create(ResolvedConfiguration $resolvedConfiguration): Conf
    {
        $conf = new Conf();

        foreach ($resolvedConfiguration->getGlobalConfigurations() as $globalConfiguration) {
            $conf->set(
                $globalConfiguration['configuration']->getKafkaProperty(),
                $globalConfiguration['resolvedValue']
            );
        }

        return $conf;
    }
}
