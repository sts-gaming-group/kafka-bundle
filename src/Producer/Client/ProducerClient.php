<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Producer\Client;

use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Producer\Contract\ProducerInterface;

class ProducerClient
{
    private ConfigurationResolver $configurationResolver;

    public function __construct(ConfigurationResolver $configurationResolver)
    {
        $this->configurationResolver = $configurationResolver;
    }

    public function produce(ProducerInterface $producer): bool
    {
        $resolvedConfiguration = $this->configurationResolver->resolveForProducer($producer);

        // todo: SA-4490

        return true;
    }
}
