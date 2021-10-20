<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Functional\Command;

use StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Producer\DummyProducerOne;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Producer\DummyProducerTwo;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ProducersDescribeCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('kafka:producers:describe');
        $this->commandTester = new CommandTester($command);
    }

    public function testConfigurationDisplayed(): void
    {
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(DummyProducerOne::class, $output);
        $this->assertStringContainsString(DummyProducerTwo::class, $output);
    }
}
