<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Type\GroupId;

class GroupIdTest extends AbstractConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new GroupId();
    }

    protected function getValidValues(): array
    {
        return ['dummy_group_id'];
    }

    protected function getInvalidValues(): array
    {
        return [1, 1.51, '', [], null, new \stdClass(), false, true];
    }
}
