<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Type\Topics;

class TopicsTest extends AbstractConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new Topics();
    }

    protected function getValidValues(): array
    {
        return [['dummy_topic_1'], ['dummy_topic_1', 'dummy_topic_2']];
    }

    protected function getInvalidValues(): array
    {
        return [1, 1.51, 'dummy_topic_1', [], null, new \stdClass(), false, true];
    }
}
