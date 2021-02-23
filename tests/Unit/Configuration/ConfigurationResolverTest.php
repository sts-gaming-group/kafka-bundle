<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Contract\ValidatedConfigurationInterface;
use Sts\KafkaBundle\Configuration\RawConfigurations;
use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationResolverTest extends TestCase
{
    private MockObject $rawConfigurations;
    private MockObject $configurationOne;
    private MockObject $configurationTwo;
    private MockObject $consumer;
    private MockObject $input;

    protected function setUp(): void
    {
        $this->rawConfigurations = $this->createMock(RawConfigurations::class);
        $this->configurationOne = $this->createMock(ConfigurationInterface::class);
        $this->configurationTwo = $this->createMock(ValidatedConfigurationInterface::class);
        $this->consumer = $this->createMock(ConsumerInterface::class);
        $this->input = $this->createMock(InputInterface::class);
    }

    public function testInputConfigurationSet(): void
    {
        $this->configurationOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->rawConfigurations->expects($this->exactly(2))
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('hasOption')
            ->willReturn(true);

        $this->input->expects($this->exactly(3))
            ->method('getOption')
            ->willReturn('value_1');

        $configurationResolver = new ConfigurationResolver($this->rawConfigurations, []);
        $configurationContainer = $configurationResolver->resolve($this->consumer, $this->input);

        $this->assertEquals('value_1', $configurationContainer->getConfiguration('config_1'));
    }

    /**
     * @dataProvider getConsumerConfigurationEmptyValues
     * @param mixed $emptyValue
     */
    public function testInputEmptyValues($emptyValue): void
    {
        if ($emptyValue === '') {
            $this->markTestSkipped('Input can still contain an empty string.');
        }

        $this->configurationOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->configurationOne->expects($this->once())
            ->method('getDefaultValue')
            ->willReturn('default_value_1');

        $this->rawConfigurations->expects($this->exactly(2))
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('hasOption')
            ->willReturn(true);

        $this->input->method('getOption')
            ->willReturn($emptyValue);

        $configurationResolver = new ConfigurationResolver($this->rawConfigurations, []);
        $configurationContainer = $configurationResolver->resolve($this->consumer, $this->input);

        $this->assertEquals('default_value_1', $configurationContainer->getConfiguration('config_1'));
    }

    public function testConfigurationSetForConsumer(): void
    {
        $this->configurationOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->rawConfigurations->expects($this->exactly(2))
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('hasOption')
            ->willReturn(false);

        $this->input->expects($this->never())
            ->method('getOption');

        $consumerClass = get_class($this->consumer);
        $configurationResolver = new ConfigurationResolver($this->rawConfigurations, [
            'consumers' => [
                $consumerClass => [
                    'config_1' => 'consumer_value_1'
                ]
            ]
        ]);
        $configurationContainer = $configurationResolver->resolve($this->consumer, $this->input);

        $this->assertEquals('consumer_value_1', $configurationContainer->getConfiguration('config_1'));
    }

    /**
     * @dataProvider getConsumerConfigurationEmptyValues
     * @param mixed $emptyValue
     */
    public function testConsumerConfigurationNullValue($emptyValue): void
    {
        $this->configurationOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->rawConfigurations->expects($this->exactly(2))
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('hasOption')
            ->willReturn(false);

        $this->input->expects($this->never())
            ->method('getOption');

        $this->configurationOne->expects($this->once())
            ->method('getDefaultValue')
            ->willReturn('default_value_1');

        $consumerClass = get_class($this->consumer);
        $configurationResolver = new ConfigurationResolver($this->rawConfigurations, [
            'consumers' => [
                $consumerClass => [
                    'config_1' => $emptyValue
                ]
            ]
        ]);
        $configurationContainer = $configurationResolver->resolve($this->consumer, $this->input);

        $this->assertEquals('default_value_1', $configurationContainer->getConfiguration('config_1'));
    }

    public function getConsumerConfigurationEmptyValues(): array
    {
        return [
            [null],
            [[]],
            ['']
        ];
    }

    public function testGlobalConfigurationSetForConsumer(): void
    {
        $this->configurationOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->rawConfigurations->expects($this->exactly(2))
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('hasOption')
            ->willReturn(false);

        $this->input->expects($this->never())
            ->method('getOption');

        $configurationResolver = new ConfigurationResolver($this->rawConfigurations, [
            'config_1' => 'global_consumer_value_1'
        ]);

        $configurationContainer = $configurationResolver->resolve($this->consumer, $this->input);

        $this->assertEquals('global_consumer_value_1', $configurationContainer->getConfiguration('config_1'));
    }

    public function testDefaultValue(): void
    {
        $this->configurationOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->rawConfigurations->expects($this->exactly(2))
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('hasOption')
            ->willReturn(false);

        $this->input->expects($this->never())
            ->method('getOption');

        $this->configurationOne->expects($this->once())
            ->method('getDefaultValue')
            ->willReturn('default_value_1');

        $configurationResolver = new ConfigurationResolver($this->rawConfigurations, []);
        $configurationContainer = $configurationResolver->resolve($this->consumer, $this->input);

        $this->assertEquals('default_value_1', $configurationContainer->getConfiguration('config_1'));
    }

    public function testValidatedConfiguration(): void
    {
        $this->configurationTwo->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->rawConfigurations->expects($this->exactly(2))
            ->method('getConfigurations')
            ->willReturn([$this->configurationTwo]);

        $this->configurationTwo->expects($this->once())
            ->method('getDefaultValue')
            ->willReturn('default_value_1');

        $this->configurationTwo->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        $this->expectException(InvalidConfigurationException::class);

        $configurationResolver = new ConfigurationResolver($this->rawConfigurations, []);
        $configurationResolver->resolve($this->consumer, $this->input);
    }
}
