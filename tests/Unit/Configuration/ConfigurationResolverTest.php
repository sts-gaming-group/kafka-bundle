<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\RawConfiguration;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationResolverTest extends TestCase
{
    private MockObject $rawConfiguration;
    private MockObject $configurationOne;
    private MockObject $configurationTwo;
    private MockObject $consumer;
    private MockObject $input;

    protected function setUp(): void
    {
        $this->rawConfiguration = $this->createMock(RawConfiguration::class);
        $this->configurationOne = $this->createMock(ConfigurationInterface::class);
        $this->configurationTwo = $this->createMock(ConfigurationInterface::class);
        $this->consumer = $this->createMock(ConsumerInterface::class);
        $this->input = $this->createMock(InputInterface::class);
    }

    public function testInputConfigurationSet(): void
    {
        $this->configurationOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->configurationOne->expects($this->once())
            ->method('isValueValid')
            ->willReturn(true);

        $this->rawConfiguration->expects($this->once())
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('getOption')
            ->willReturn('value_1');

        $configurationResolver = new ConfigurationResolver($this->rawConfiguration, []);
        $resolvedConfiguration = $configurationResolver->resolve($this->consumer, $this->input);

        $this->assertEquals('value_1', $resolvedConfiguration->getConfigurationValue('config_1'));
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

        $this->configurationOne->expects($this->once())
            ->method('getName')
            ->willReturn('config_1');

        $this->configurationOne->expects($this->never())
            ->method('isValueValid');

        $this->configurationOne->expects($this->never())
            ->method('getDescription');

        $this->rawConfiguration->expects($this->once())
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('getParameterOption')
            ->willReturn(false);

        $this->input->method('getOption')
            ->willReturn($emptyValue);

        $this->expectException(InvalidConfigurationException::class);
        $configurationResolver = new ConfigurationResolver($this->rawConfiguration, []);
        $configurationResolver->resolve($this->consumer, $this->input);
    }

    public function testConfigurationSetForConsumer(): void
    {
        $this->configurationOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->rawConfiguration->expects($this->once())
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('getParameterOption')
            ->willReturn(false);

        $this->input->expects($this->never())
            ->method('getOption');

        $consumerClass = get_class($this->consumer);
        $configurationResolver = new ConfigurationResolver($this->rawConfiguration, [
            'consumers' => [
                $consumerClass => [
                    'config_1' => 'consumer_value_1'
                ]
            ]
        ]);
        $resolvedConfiguration = $configurationResolver->resolve($this->consumer, $this->input);

        $this->assertEquals('consumer_value_1', $resolvedConfiguration->getConfigurationValue('config_1'));
    }

    public function getConsumerConfigurationEmptyValues(): array
    {
        return [
            [null],
            ['']
        ];
    }

    public function testGlobalConfigurationSetForConsumer(): void
    {
        $this->configurationOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('config_1');

        $this->configurationOne->expects($this->never())
            ->method('isValueValid');

        $this->rawConfiguration->expects($this->once())
            ->method('getConfigurations')
            ->willReturn([$this->configurationOne]);

        $this->input->expects($this->once())
            ->method('getParameterOption')
            ->willReturn(false);

        $configurationResolver = new ConfigurationResolver($this->rawConfiguration, [
            'config_1' => 'global_consumer_value_1'
        ]);

        $resolvedConfiguration = $configurationResolver->resolve($this->consumer, $this->input);

        $this->assertEquals('global_consumer_value_1', $resolvedConfiguration->getConfigurationValue('config_1'));
    }
}
