<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Type\Brokers;

class BrokersTest extends AbstractConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new Brokers();
    }

    protected function getValidValues(): array
    {
        return [['127.0.0.1'], ['dummy_broker_1', 'dummy_broker_2']];
    }

    protected function getInvalidValues(): array
    {
        return [1, 1.51, '127.0.0.1', [], null, new \stdClass(), false, true];
    }
}
