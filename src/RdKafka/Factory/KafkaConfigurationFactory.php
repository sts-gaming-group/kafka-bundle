<?php

namespace StsGamingGroup\KafkaBundle\RdKafka\Factory;

use RdKafka\Conf;
use StsGamingGroup\KafkaBundle\Client\Contract\CallableInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ClientInterface;
use StsGamingGroup\KafkaBundle\Configuration\ConfigurationResolver;
use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use Symfony\Component\Console\Input\InputInterface;

class KafkaConfigurationFactory
{
    private ConfigurationResolver $configurationResolver;

    public function __construct(ConfigurationResolver $configurationResolver)
    {
        $this->configurationResolver = $configurationResolver;
    }

    public function create(ClientInterface $client, ?InputInterface $input = null): Conf
    {
        $configuration = $this->configurationResolver->resolve($client, $input);
        $conf = new Conf();

        foreach ($configuration->getConfigurations(ResolvedConfiguration::KAFKA_TYPES) as $kafkaConfiguration) {
            $resolvedValue = $kafkaConfiguration['resolvedValue'];
            $value = is_array($resolvedValue) ? implode(',', $resolvedValue) : $resolvedValue;
            $conf->set(
                $kafkaConfiguration['configuration']->getKafkaProperty(),
                $value
            );
        }

        if ($client instanceof CallableInterface) {
            $callbacks = $client->callbacks();
            foreach ($callbacks as $name => $callback) {
                $conf->{$name}($callback);
            }
        }

        return $conf;
    }
}
