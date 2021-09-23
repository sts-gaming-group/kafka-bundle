<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Type\ProducerTopic;

class ProducerTopicTest extends AbstractConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new ProducerTopic();
    }

    protected function getValidValues(): array
    {
        return ['a', '123'];
    }

    protected function getInvalidValues(): array
    {
        return [1.51, 1, '', [], null, new \stdClass(), false, true];
    }
}
