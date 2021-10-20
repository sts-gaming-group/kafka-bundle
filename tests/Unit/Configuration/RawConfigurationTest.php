<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\RawConfiguration;
use StsGamingGroup\KafkaBundle\Configuration\Exception\InvalidConfigurationException;

class RawConfigurationTest extends TestCase
{
    private MockObject $configurationOne;
    private MockObject $configurationTwo;

    protected function setUp(): void
    {
        $this->configurationOne = $this->createMock(ConfigurationInterface::class);
        $this->configurationTwo = $this->createMock(ConfigurationInterface::class);
    }

    public function testAddConfiguration(): void
    {
        $this->configurationOne->method('getName')
            ->willReturn('configuration_1');

        $this->configurationTwo->method('getName')
            ->willReturn('configuration_2');

        $rawConfiguration = new RawConfiguration();
        $rawConfiguration->addConfiguration($this->configurationOne)
            ->addConfiguration($this->configurationTwo);

        $this->assertCount(2, $rawConfiguration->getConfigurations());
    }

    public function testValidation(): void
    {
        $this->configurationOne->method('getName')
            ->willReturn('configuration_1');

        $this->configurationTwo->method('getName')
            ->willReturn('configuration_1');

        $this->expectException(InvalidConfigurationException::class);
        $rawConfiguration = new RawConfiguration();
        $rawConfiguration->addConfiguration($this->configurationOne)
            ->addConfiguration($this->configurationTwo);
    }
}
