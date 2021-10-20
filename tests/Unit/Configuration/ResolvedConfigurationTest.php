<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\KafkaConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ProducerConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Exception\InvalidConfigurationType;
use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;

class ResolvedConfigurationTest extends TestCase
{
    private MockObject $kafkaConfigurationOne;
    private MockObject $kafkaConfigurationTwo;
    private MockObject $consumerConfigurationOne;
    private MockObject $consumerConfigurationTwo;
    private MockObject $producerConfigurationOne;
    private MockObject $producerConfigurationTwo;

    protected function setUp(): void
    {
        $this->kafkaConfigurationOne = $this->createMock(KafkaConfigurationInterface::class);
        $this->kafkaConfigurationTwo = $this->createMock(KafkaConfigurationInterface::class);
        $this->consumerConfigurationOne = $this->createMock(ConsumerConfigurationInterface::class);
        $this->consumerConfigurationTwo = $this->createMock(ConsumerConfigurationInterface::class);
        $this->producerConfigurationOne = $this->createMock(ProducerConfigurationInterface::class);
        $this->producerConfigurationTwo = $this->createMock(ProducerConfigurationInterface::class);
    }

    /**
     * @dataProvider getConfigurationsByTypeProvider
     */
    public function testGetConfigurationsByType(string $name, int $expectedCount, string $interface): void
    {
        $this->createDefaultExpectations();
        $resolved = $this->createDefaultResolvedConfiguration();
        $configurations = $resolved->getConfigurations($name);

        $this->assertCount($expectedCount, $configurations);

        foreach ($configurations as $configuration) {
            $this->assertInstanceOf($interface, $configuration['configuration']);
        }
    }

    public function getConfigurationsByTypeProvider(): array
    {
        return [
            ['all', 6, ConfigurationInterface::class],
            ['kafka', 2, KafkaConfigurationInterface::class],
            ['consumer', 2, ConsumerConfigurationInterface::class],
            ['producer', 2, ProducerConfigurationInterface::class]
        ];
    }

    public function testGetValue(): void
    {
        $this->createDefaultExpectations();
        $resolved = $this->createDefaultResolvedConfiguration();

        $this->assertEquals('faz', $resolved->getValue('cc_one'));
    }

    public function testUnknownConfigurationType(): void
    {
        $this->expectException(InvalidConfigurationType::class);

        $resolved = new ResolvedConfiguration();

        $resolved->getConfigurations('foo');
    }

    private function createDefaultExpectations(): void
    {
        $this->kafkaConfigurationOne
            ->method('getName')
            ->willReturn('kc_one');

        $this->kafkaConfigurationTwo
            ->method('getName')
            ->willReturn('kc_two');

        $this->consumerConfigurationOne
            ->method('getName')
            ->willReturn('cc_one');

        $this->consumerConfigurationTwo
            ->method('getName')
            ->willReturn('cc_two');

        $this->producerConfigurationOne
            ->method('getName')
            ->willReturn('pc_one');

        $this->producerConfigurationTwo
            ->method('getName')
            ->willReturn('pc_two');
    }

    private function createDefaultResolvedConfiguration(): ResolvedConfiguration
    {
        return (new ResolvedConfiguration())
            ->addConfiguration($this->kafkaConfigurationOne, 'foo')
            ->addConfiguration($this->kafkaConfigurationTwo, 'boo')
            ->addConfiguration($this->consumerConfigurationOne, 'faz')
            ->addConfiguration($this->consumerConfigurationTwo, 'baz')
            ->addConfiguration($this->producerConfigurationOne, 'fee')
            ->addConfiguration($this->producerConfigurationTwo, 'bee');
    }
}
