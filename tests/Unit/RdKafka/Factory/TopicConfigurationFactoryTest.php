<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\RdKafka\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Configuration\Type\AutoCommitInterval;
use Sts\KafkaBundle\Configuration\Type\AutoOffsetReset;
use Sts\KafkaBundle\Configuration\Type\OffsetStoreMethod;
use Sts\KafkaBundle\RdKafka\Factory\TopicConfigurationFactory;

class TopicConfigurationFactoryTest extends TestCase
{
    private MockObject $configurationContainer;

    protected function setUp(): void
    {
        $this->configurationContainer = $this->createMock(ConfigurationContainer::class);
    }

    public function testTopicConfigurationCreated(): void
    {
        $this->configurationContainer->expects($this->exactly(3))
            ->method('getConfiguration')
            ->withConsecutive([AutoCommitInterval::NAME], [OffsetStoreMethod::NAME], [AutoOffsetReset::NAME])
            ->willReturnOnConsecutiveCalls('555', OffsetStoreMethod::BROKER, AutoOffsetReset::SMALLEST);

        $topicConfigurationFactory = new TopicConfigurationFactory();
        $topicConf = $topicConfigurationFactory->create($this->configurationContainer);
        $configs = $topicConf->dump();

        $this->assertEquals('555', $configs['auto.commit.interval.ms']);
        $this->assertEquals(OffsetStoreMethod::BROKER, $configs['offset.store.method']);
        $this->assertEquals(AutoOffsetReset::SMALLEST, $configs['auto.offset.reset']);
    }
}
