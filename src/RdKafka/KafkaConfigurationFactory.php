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

        foreach ($configuration->getConfigurations(ResolvedConfiguration::KAFKA_TYPES) as $kafkaConfiguration) {
            $resolvedValue = $kafkaConfiguration['resolvedValue'];
            $value = is_array($resolvedValue) ? implode(',', $resolvedValue) : $resolvedValue;
            $conf->set(
                $kafkaConfiguration['configuration']->getKafkaProperty(),
                $value
            );
        }
        $conf->setConsumeCb(function(\RdKafka\Message  $message) {
            dump('consume callback', $message);
        });

        $conf->setErrorCb(function($kafka, $err, $reason) {
            dump('error callback', $err);
        });

        $conf->setLogCb(function($kafka, $level, $facility, $message) {
            dump('log callback', $err);
        });
//        dump($conf->setErrorCb());
        $this->conf = $conf;

        return $conf;
    }
}
