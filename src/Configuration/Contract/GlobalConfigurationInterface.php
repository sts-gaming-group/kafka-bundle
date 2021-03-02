<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Contract;

interface GlobalConfigurationInterface extends ConfigurationInterface
{
    public function getKafkaProperty(): string;
}
