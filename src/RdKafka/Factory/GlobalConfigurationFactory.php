<?php

namespace Sts\KafkaBundle\RdKafka\Factory;

use RdKafka\Conf;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

class GlobalConfigurationFactory
{
    private ?Conf $conf = null;

    public function create(ResolvedConfiguration $resolvedConfiguration): Conf
    {
        if ($this->conf) {
            return $this->conf;
        }

        $conf = new Conf();

        foreach ($resolvedConfiguration->getGlobalConfigurations() as $globalConfiguration) {
            $resolvedValue = $globalConfiguration['resolvedValue'];
            $value = is_array($resolvedValue) ? implode(',', $resolvedValue) : $resolvedValue;
            $conf->set(
                $globalConfiguration['configuration']->getKafkaProperty(),
                $value
            );
        }

        $this->conf = $conf;

        return $conf;
    }
}
