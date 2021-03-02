<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Configuration\Contract\GlobalConfigurationInterface;
use Sts\KafkaBundle\Configuration\Contract\TopicConfigurationInterface;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

class ResolveConfigurationTest extends TestCase
{
    private MockObject $topicConfigurationOne;
    private MockObject $topicConfigurationTwo;
    private MockObject $globalConfigurationOne;
    private MockObject $globalConfigurationTwo;
    private ResolvedConfiguration $resolvedConfiguration;

    protected function setUp(): void
    {
        $this->topicConfigurationOne = $this->createMock(TopicConfigurationInterface::class);
        $this->topicConfigurationTwo = $this->createMock(TopicConfigurationInterface::class);
        $this->globalConfigurationOne = $this->createMock(GlobalConfigurationInterface::class);
        $this->globalConfigurationTwo = $this->createMock(GlobalConfigurationInterface::class);
        $this->resolvedConfiguration = new ResolvedConfiguration();
    }

    public function testAddAndGetConfigurations(): void
    {
        $this->topicConfigurationOne->expects($this->once())
            ->method('getName')
            ->willReturn('config_1');

        $this->globalConfigurationTwo->expects($this->once())
            ->method('getName')
            ->willReturn('config_2');

        $this->resolvedConfiguration->addConfiguration($this->topicConfigurationOne, 'value_1')
            ->addConfiguration($this->globalConfigurationTwo, 'value_2');

        $this->assertCount(2, $this->resolvedConfiguration->getConfigurations());
    }

    public function testGetConfigurationByName(): void
    {
        $this->topicConfigurationOne->expects($this->once())
            ->method('getName')
            ->willReturn('config_1');

        $this->globalConfigurationTwo->expects($this->once())
            ->method('getName')
            ->willReturn('config_2');

        $this->resolvedConfiguration->addConfiguration($this->topicConfigurationOne, 'value_1')
            ->addConfiguration($this->globalConfigurationTwo, 'value_2');

        $this->assertEquals(
            'value_2',
            $this->resolvedConfiguration->getConfigurationValue('config_2')
        );
    }

    public function testGetTopicConfigurations(): void
    {
        $this->topicConfigurationOne->expects($this->once())
            ->method('getName')
            ->willReturn('config_1');

        $this->topicConfigurationTwo->expects($this->once())
            ->method('getName')
            ->willReturn('config_2');

        $this->globalConfigurationOne->expects($this->once())
            ->method('getName')
            ->willReturn('config_3');

        $this->globalConfigurationTwo->expects($this->once())
            ->method('getName')
            ->willReturn('config_4');

        $this->resolvedConfiguration->addConfiguration($this->topicConfigurationOne, 'value_1')
            ->addConfiguration($this->topicConfigurationTwo, 'value_2')
            ->addConfiguration($this->globalConfigurationOne, 'value_3')
            ->addConfiguration($this->globalConfigurationTwo, 'value_4');

        $topicConfigurations = $this->resolvedConfiguration->getTopicConfigurations();
        $this->assertCount(2, $topicConfigurations);
        foreach ($topicConfigurations as $topicConfiguration) {
            $this->assertInstanceOf(TopicConfigurationInterface::class, $topicConfiguration['configuration']);
        }
    }

    public function testGetGlobalConfigurations(): void
    {
        $this->globalConfigurationOne->expects($this->once())
            ->method('getName')
            ->willReturn('config_1');

        $this->globalConfigurationTwo->expects($this->once())
            ->method('getName')
            ->willReturn('config_2');

        $this->topicConfigurationOne->expects($this->once())
            ->method('getName')
            ->willReturn('config_3');

        $this->topicConfigurationTwo->expects($this->once())
            ->method('getName')
            ->willReturn('config_4');

        $this->resolvedConfiguration->addConfiguration($this->globalConfigurationOne, 'value_1')
            ->addConfiguration($this->globalConfigurationTwo, 'value_2')
            ->addConfiguration($this->topicConfigurationOne, 'value_3')
            ->addConfiguration($this->topicConfigurationTwo, 'value_4');

        $topicConfigurations = $this->resolvedConfiguration->getGlobalConfigurations();
        $this->assertCount(2, $topicConfigurations);
        foreach ($topicConfigurations as $topicConfiguration) {
            $this->assertInstanceOf(GlobalConfigurationInterface::class, $topicConfiguration['configuration']);
        }
    }
}
