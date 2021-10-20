<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Contract;

interface KafkaConfigurationInterface extends ConfigurationInterface
{
    public function getKafkaProperty(): string;
}
