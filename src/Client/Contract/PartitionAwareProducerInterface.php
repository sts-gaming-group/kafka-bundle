<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Contract;

use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;

interface PartitionAwareProducerInterface extends ProducerInterface
{
    /**
     * @param mixed $data
     * @param ResolvedConfiguration $configuration
     * @return int
     */
    public function getPartition($data, ResolvedConfiguration $configuration): int;
}
