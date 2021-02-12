<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Functional\Command;

use Sts\KafkaBundle\Tests\Functional\BaseKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class KafkaConsumerInfoCommandTest extends BaseKernelTestCase
{
    public function testConsumersFound(): void
    {
        $kernel = static::bootKernel(['test_config' => __DIR__ . '/config/consumer_info_command.xml']);
        $application = new Application($kernel);
        $command = $application->find('kafka:consumers:info');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('MockConsumerOne', $output);
        $this->assertStringContainsString('MockConsumerTwo', $output);
    }

    public function testNoConsumersFound(): void
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('kafka:consumers:info');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('No registered consumers', $output);
    }
}
