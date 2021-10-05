<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Type\AutoCommitIntervalMs;

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
