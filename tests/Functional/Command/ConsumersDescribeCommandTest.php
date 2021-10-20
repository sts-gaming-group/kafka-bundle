<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Functional\Command;

use StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Consumer\DummyConsumerOne;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Consumer\DummyConsumerTwo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumersDescribeCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('kafka:consumers:describe');
        $this->commandTester = new CommandTester($command);
    }

    public function testConfigurationDisplayed(): void
    {
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(DummyConsumerOne::class, $output);
        $this->assertStringContainsString(DummyConsumerTwo::class, $output);
    }

    public function testConfigurationDisplayedByName(): void
    {
        $this->commandTester->execute(['--name' => 'dummy_consumer_one']);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(DummyConsumerOne::class, $output);
        $this->assertStringNotContainsString(DummyConsumerTwo::class, $output);
    }
}
