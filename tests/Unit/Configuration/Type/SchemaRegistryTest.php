<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Type\SchemaRegistry;

class SchemaRegistryTest extends AbstractConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new SchemaRegistry();
    }

    protected function getValidValues(): array
    {
        return ['127.0.0.1', 'url'];
    }

    protected function getInvalidValues(): array
    {
        return [1.51, 1, '', [], null, new \stdClass(), false, true];
    }
}
