<?php

namespace Sts\KafkaBundle\RdKafka;

use RdKafka\Conf;
use Sts\KafkaBundle\Client\Contract\ClientInterface;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

class KafkaConfigurationFactory
{
    private ?Conf $conf = null;
    private ConfigurationResolver $configurationResolver;

    public function __construct(ConfigurationResolver $configurationResolver)
    {
        $this->configurationResolver = $configurationResolver;
    }

    public function create(ClientInterface $client): Conf
    {
        if ($this->conf) {
            return $this->conf;
        }

        $configuration = $this->configurationResolver->resolve($client);
        $conf = new Conf();

        foreach ($configuration->getConfigurations(ResolvedConfiguration::KAFKA_TYPES) as $globalConfiguration) {
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
