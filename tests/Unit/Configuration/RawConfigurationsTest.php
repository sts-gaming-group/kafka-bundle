<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\RawConfigurations;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;

class RawConfigurationsTest extends TestCase
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

        $rawConfigurations = new RawConfigurations();
        $rawConfigurations->addConfiguration($this->configurationOne)
            ->addConfiguration($this->configurationTwo);

        $this->assertCount(2, $rawConfigurations->getConfigurations());
    }

    public function testGetConfigurationByName(): void
    {
        $this->configurationOne->method('getName')
            ->willReturn('configuration_1');

        $this->configurationTwo->method('getName')
            ->willReturn('configuration_2');

        $rawConfigurations = new RawConfigurations();
        $rawConfigurations->addConfiguration($this->configurationOne)
            ->addConfiguration($this->configurationTwo);

        $this->assertInstanceOf(
            get_class($this->configurationTwo),
            $rawConfigurations->getConfigurationByName('configuration_2')
        );
    }

    public function testConfigurationExists(): void
    {
        $this->configurationOne->method('getName')
            ->willReturn('configuration_1');

        $this->configurationTwo->method('getName')
            ->willReturn('configuration_2');

        $rawConfigurations = new RawConfigurations();
        $rawConfigurations->addConfiguration($this->configurationOne)
            ->addConfiguration($this->configurationTwo);

        $this->assertTrue($rawConfigurations->configurationExists('configuration_2'));
        $this->assertFalse($rawConfigurations->configurationExists('configuration_3'));
    }

    public function testValidation(): void
    {
        $this->configurationOne->method('getName')
            ->willReturn('configuration_1');

        $this->configurationTwo->method('getName')
            ->willReturn('configuration_1');

        $this->expectException(InvalidConfigurationException::class);
        $rawConfigurations = new RawConfigurations();
        $rawConfigurations->addConfiguration($this->configurationOne)
            ->addConfiguration($this->configurationTwo);
    }
}
