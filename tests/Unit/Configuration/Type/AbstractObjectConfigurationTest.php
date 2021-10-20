<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

abstract class AbstractObjectConfigurationTest extends AbstractConfigurationTest
{
    abstract protected function getObject(): object;

    protected function getValidValues(): array
    {
        return [$this->getObject(), get_class($this->getObject())];
    }

    protected function getInvalidValues(): array
    {
        return [1, 1.51, '1', [], null, new \stdClass(), false, true];
    }
}
