<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Type\AutoCommitIntervalMs;

class AutoCommitIntervalMsTest extends AbstractConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new AutoCommitIntervalMs();
    }

    protected function getValidValues(): array
    {
        return ['1', '1.51'];
    }

    protected function getInvalidValues(): array
    {
        return [1, 1.51, 'a', [], null, new \stdClass(), false, true];
    }
}
