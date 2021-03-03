<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use Sts\KafkaBundle\Client\Contract\ProducerInterface;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class ProducerClient
{
    use CheckForRdKafkaExtensionTrait;

    private ConfigurationResolver $configurationResolver;

    public function __construct(ConfigurationResolver $configurationResolver)
    {
        $this->configurationResolver = $configurationResolver;
    }

    public function produce(ProducerInterface $producer): bool
    {
        $this->isKafkaExtensionLoaded();

        $resolvedConfiguration = $this->configurationResolver->resolve($producer);

        // todo: SA-4490

        return true;
    }
}
