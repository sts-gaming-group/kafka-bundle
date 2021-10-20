<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Traits;

trait CheckForRdKafkaExtensionTrait
{
    protected function isKafkaExtensionLoaded(): void
    {
        if (!extension_loaded('rdkafka')) {
            throw new \RuntimeException('rdkafka extension missing in PHP');
        }
    }
}
