<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

use PHPUnit\Framework\TestCase;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Type\AutoCommitIntervalMs;

abstract class AbstractConfigurationTest extends TestCase
{
    abstract protected function getConfiguration(): ConfigurationInterface;

    abstract protected function getValidValues(): array;

    abstract protected function getInvalidValues(): array;

    /**
     * @dataProvider getValidValuesProvider
     * @param mixed $value
     */
    public function testValidValue($value): void
    {
        $configuration = $this->getConfiguration();

        $this->assertTrue($configuration->isValueValid($value));
    }

    /**
     * @dataProvider getInvalidValuesProvider
     * @param mixed $value
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
