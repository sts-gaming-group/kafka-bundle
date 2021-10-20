<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

abstract class AbstractBooleanConfigurationTest extends AbstractConfigurationTest
{
    protected function getValidValues(): array
    {
        return ['true', 'false'];
    }

    protected function getInvalidValues(): array
    {
        return [1, 1.51, 'True', 'False', [], null, new \stdClass(), false, true];
    }
}
