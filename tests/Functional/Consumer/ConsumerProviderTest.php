<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Functional\Consumer;

use Sts\KafkaBundle\Consumer\ConsumerProvider;
use Sts\KafkaBundle\Tests\Dummy\DummyConsumerOne;
use Sts\KafkaBundle\Tests\Dummy\DummyConsumerTwo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConsumerProviderTest extends KernelTestCase
{
    public function testConsumersRegistered(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        /** @var ConsumerProvider $consumerProvider */
        $consumerProvider = $container->get('sts_kafka.consumer.provider');

        $this->assertEquals(DummyConsumerOne::NAME, $consumerProvider->provide(DummyConsumerOne::NAME)->getName());
        $this->assertEquals(DummyConsumerTwo::NAME, $consumerProvider->provide(DummyConsumerTwo::NAME)->getName());
    }
}
