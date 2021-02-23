<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Configuration\ConfigurationContainer;

class ConfigurationContainerTest extends TestCase
{
    public function testAddAndGetConfigurations(): void
    {
        $configurationContainer = new ConfigurationContainer();
        $configurationContainer->addConfiguration('config_1', 'value_1')
            ->addConfiguration('config_2', 'value_2');

        $this->assertCount(2, $configurationContainer->getConfigurations());
    }

    public function testGetConfigurationByName(): void
    {
        $configurationContainer = new ConfigurationContainer();
        $configurationContainer->addConfiguration('config_1', 'value_1')
            ->addConfiguration('config_2', 'value_2');

        $this->assertEquals('value_2', $configurationContainer->getConfiguration('config_2'));
    }

    public function testConfigurationExists(): void
    {
        $configurationContainer = new ConfigurationContainer();
        $configurationContainer->addConfiguration('config_1', 'value_1')
            ->addConfiguration('config_2', 'value_2');

        $this->assertTrue( $configurationContainer->exists('config_1'));
        $this->assertFalse( $configurationContainer->exists('config_3'));
    }
}
