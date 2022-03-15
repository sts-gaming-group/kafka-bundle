<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Type\StatisticsIntervalMs;

class StatisticsIntervalMsTest extends AbstractConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new StatisticsIntervalMs();
    }

    protected function getValidValues(): array
    {
        return ['1', 2, 1000, '1000', 5000, 30000, 100000];
    }

    protected function getInvalidValues(): array
    {
        return [-1, '-1', 1.51, '2.55', '', [], null, new \stdClass(), false, true];
    }
}
