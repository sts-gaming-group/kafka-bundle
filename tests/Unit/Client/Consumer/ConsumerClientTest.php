<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Client\Consumer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Client\Consumer\ConsumerClient;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Client\Consumer\Factory\MessageFactory;
use Sts\KafkaBundle\RdKafka\Factory\KafkaConfigurationFactory;
use Sts\KafkaBundle\Tests\Dummy\Client\Consumer\DummyConsumerOne;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConsumerClientTest extends TestCase
{
    private MockObject $kafkaConfigurationFactory;
    private MockObject $messageFactory;
    private MockObject $configurationResolver;
    private MockObject $eventDispatcher;

    private ConsumerClient $client;

    protected function setUp(): void
    {
        $this->kafkaConfigurationFactory = $this->createMock(KafkaConfigurationFactory::class);
        $this->messageFactory = $this->createMock(MessageFactory::class);
        $this->configurationResolver = $this->createMock(ConfigurationResolver::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->client = new ConsumerClient(
            $this->kafkaConfigurationFactory,
            $this->messageFactory,
            $this->configurationResolver,
            $this->eventDispatcher
        );
    }
    // TODO: maybe some tests
}
