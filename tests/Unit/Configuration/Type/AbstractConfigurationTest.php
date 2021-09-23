<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration\Type;

use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Type\AutoCommitIntervalMs;

abstract class AbstractConfigurationTest extends TestCase
{
    abstract protected function getConfiguration(): ConfigurationInterface;

    abstract protected function getValidValues(): array;

    abstract protected function getInvalidValues(): array;

    /**
     * @dataProvider getValidValuesProvider
     */
    public function testValidValue($value): void
    {
        $configuration = $this->getConfiguration();

        $this->assertTrue($configuration->isValueValid($value));
    }

    /**
     * @dataProvider getInvalidValuesProvider
     */
    public function testInvalidValue($value): void
    {
        $configuration = $this->getConfiguration();

        $this->assertFalse($configuration->isValueValid($value));
    }

    public function getValidValuesProvider(): array
    {
        $values = $this->getValidValues();
        $provided = [];

        foreach ($values as $value) {
            $provided[] = [$value];
        }

        return $provided;
    }

    public function getInvalidValuesProvider(): array
    {
        $values = $this->getInvalidValues();
        $provided = [];

        foreach ($values as $value) {
            $provided[] = [$value];
        }

        return $provided;
    }
}
