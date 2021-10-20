<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Functional\Client\Consumer;

use StsGamingGroup\KafkaBundle\Client\Consumer\ConsumerProvider;
use StsGamingGroup\KafkaBundle\Client\Consumer\Exception\InvalidConsumerException;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Consumer\DummyConsumerOne;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Consumer\DummyConsumerOneClone;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Client\Consumer\DummyConsumerTwo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConsumerProviderTest extends KernelTestCase
{
    private ConsumerProvider $provider;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $this->provider = $container->get('sts_gaming_group_kafka.client.consumer.consumer_provider');
    }

    public function testConsumersRegistered(): void
    {
        $consumerOne = $this->provider->provide(DummyConsumerOne::NAME);
        $consumerTwo = $this->provider->provide(DummyConsumerTwo::NAME);

        $this->assertEquals(DummyConsumerOne::NAME, $consumerOne->getName());
        $this->assertEquals(DummyConsumerTwo::NAME, $consumerTwo->getName());
    }

    public function testMoreThanOneConsumerFound(): void
    {
        $this->provider->addConsumer(new DummyConsumerOneClone());

        $this->expectException(InvalidConsumerException::class);
        $this->expectExceptionMessageMatches('/Multiple consumers found/');

        $this->provider->provide(DummyConsumerOneClone::NAME);
    }

    public function testNoConsumersFound(): void
    {
        $this->expectException(InvalidConsumerException::class);
        $this->expectExceptionMessageMatches('/There is no matching consumer/');

        $this->provider->provide('foo');
    }

    public function testGetAllConsumers(): void
    {
        $this->assertCount(2, $this->provider->getAll());
    }
}
