<?php

namespace StsGamingGroup\KafkaBundle\Configuration\Traits;

use ReflectionClass;
use StsGamingGroup\KafkaBundle\Client\Contract\ClientInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;

trait SupportsConsumerTrait
{
    public function supportsClient(ClientInterface $client): bool
    {
        $clientRef = new ReflectionClass($client::class);

        return $clientRef->implementsInterface(ConsumerInterface::class);
    }
}
