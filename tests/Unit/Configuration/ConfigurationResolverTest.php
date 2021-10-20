<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use StsGamingGroup\KafkaBundle\Configuration\ConfigurationResolver;
use StsGamingGroup\KafkaBundle\Configuration\Contract\CastValueInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Exception\InvalidClientException;
use StsGamingGroup\KafkaBundle\Configuration\RawConfiguration;
use StsGamingGroup\KafkaBundle\Configuration\Exception\InvalidConfigurationException;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Consumer\DummyConsumerOne;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Consumer\DummyConsumerThree;
use Symfony\Component\Console\Input\Input;

class ConfigurationResolverTest extends TestCase
{
    private MockObject $configurationOne;
    private MockObject $configurationTwo;
    private MockObject $configurationThree;
    private MockObject $input;
    private array $yamlConfig;

    protected function setUp(): void
    {
        $this->configurationOne = $this->createMock(ConfigurationInterface::class);
        $this->configurationTwo = $this->createMock(ConfigurationInterface::class);
        $this->configurationThree = $this->createMock(CastValueInterface::class);
        $this->input = $this->createMock(Input::class);
        $this->yamlConfig = json_decode(file_get_contents(__DIR__ . '/../../config/consumers.json'), true);
    }

    public function testDefaultValue(): void
    {
        $this->configurationOne
            ->method('getName')
            ->willReturn('configuration_one');

        $this->configurationOne
            ->method('getDefaultValue')
            ->willReturn('foo');

        $rawConfiguration = (new RawConfiguration())
            ->addConfiguration($this->configurationOne);

        $resolver = new ConfigurationResolver($rawConfiguration, $this->yamlConfig);
        $resolved = $resolver->resolve(DummyConsumerOne::class);

        $this->assertEquals('foo', $resolved->getValue('configuration_one'));
    }

    public function testValueCasted(): void
    {
        $this->configurationThree
            ->method('getName')
            ->willReturn('configuration_three');

        $this->configurationThree
            ->method('getDefaultValue')
            ->willReturn('1');

        $this->configurationThree
            ->method('cast')
            ->willReturn(1);

        $rawConfiguration = (new RawConfiguration())
            ->addConfiguration($this->configurationThree);

        $resolver = new ConfigurationResolver($rawConfiguration, $this->yamlConfig);
        $resolved = $resolver->resolve(DummyConsumerOne::class);

        $this->assertEquals(1, $resolved->getValue('configuration_three'));
    }

    public function testWrongClientException(): void
    {
        $rawConfiguration = (new RawConfiguration())
            ->addConfiguration($this->configurationOne);

        $resolver = new ConfigurationResolver($rawConfiguration, $this->yamlConfig);

        $this->expectException(InvalidClientException::class);

        $resolver->resolve('foo');
    }
    public function testInputValue(): void
    {
        $this->configurationOne
            ->method('getName')
            ->willReturn('configuration_one');

        $this->configurationOne
            ->method('isValueValid')
            ->willReturn(true);

        $this->input
            ->method('getParameterOption')
            ->willReturn('bar');

        $this->input
            ->method('getOption')
            ->willReturn('bar');

        $rawConfiguration = (new RawConfiguration())
            ->addConfiguration($this->configurationOne);

        $resolver = new ConfigurationResolver($rawConfiguration, $this->yamlConfig);
        $resolved = $resolver->resolve(DummyConsumerOne::class, $this->input);

        $this->assertEquals('bar', $resolved->getValue('configuration_one'));
    }

    public function testInputValueInvalid(): void
    {
        $this->configurationOne
            ->method('getName')
            ->willReturn('configuration_one');

        $this->configurationOne
            ->method('isValueValid')
            ->willReturn(false);

        $this->input
            ->method('getParameterOption')
            ->willReturn('bar');

        $this->input
            ->method('getOption')
            ->willReturn('bar');

        $rawConfiguration = (new RawConfiguration())
            ->addConfiguration($this->configurationOne);

        $resolver = new ConfigurationResolver($rawConfiguration, $this->yamlConfig);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectDeprecationMessageMatches('/configuration_one/');

        $resolver->resolve(DummyConsumerOne::class, $this->input);
    }

    public function testYamlConfig(): void
    {
        $this->configurationOne
            ->method('getName')
            ->willReturn('group_id');

        $this->configurationOne
            ->method('isValueValid')
            ->willReturn(true);

        $rawConfiguration = (new RawConfiguration())
            ->addConfiguration($this->configurationOne);

        $resolver = new ConfigurationResolver($rawConfiguration, $this->yamlConfig);
        $resolved = $resolver->resolve(DummyConsumerOne::class);

        $this->assertEquals('dummy_group_id_one', $resolved->getValue('group_id'));
    }

    public function testYamlConfigInvalid(): void
    {
        $this->configurationOne
            ->method('getName')
            ->willReturn('group_id');

        $this->configurationOne
            ->method('isValueValid')
            ->willReturn(false);

        $rawConfiguration = (new RawConfiguration())
            ->addConfiguration($this->configurationOne);

        $resolver = new ConfigurationResolver($rawConfiguration, $this->yamlConfig);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectDeprecationMessageMatches('/group_id/');

        $resolver->resolve(DummyConsumerOne::class);
    }

    public function testParentYamlConfig(): void
    {
        $this->configurationOne
            ->method('getName')
            ->willReturn('group_id');

        $this->configurationOne
            ->method('isValueValid')
            ->willReturn(true);

        $rawConfiguration = (new RawConfiguration())
            ->addConfiguration($this->configurationOne);

        $resolver = new ConfigurationResolver($rawConfiguration, $this->yamlConfig);
        $resolved = $resolver->resolve(DummyConsumerThree::class);

        $this->assertEquals('dummy_group_id_two', $resolved->getValue('group_id'));
    }
}
