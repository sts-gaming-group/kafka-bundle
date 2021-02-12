<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private Processor $processor;

    protected function setUp(): void
    {
        $this->processor = new Processor();
    }

    public function testDefaultConfiguration(): void
    {
        $processedConfig = $this->getProcessedConfig([]);

        $this->assertEquals([
            'brokers' => [],
            'schema_registry' => '127.0.0.1',
            'group_id' => 'sts_kafka',
            'enable_auto_offset_store' => true,
            'enable_auto_commit' => true
        ],
            $processedConfig
        );
    }

    public function testBrokersConfig(): void
    {
        $brokers = ['123.0.0.1', '132.0.0.1'];
        $processedConfig = $this->getProcessedConfig(['brokers' => $brokers]);

        $this->assertEquals($brokers, $processedConfig['brokers']);
    }

    public function testSchemaRegistryConfig(): void
    {
        $schemaRegistry = '123.0.0.1';
        $processedConfig = $this->getProcessedConfig(['schema_registry' => $schemaRegistry]);

        $this->assertEquals($schemaRegistry, $processedConfig['schema_registry']);
    }

    public function testGroupIdConfig(): void
    {
        $groupId = 'test_group';
        $processedConfig = $this->getProcessedConfig(['group_id' => $groupId]);

        $this->assertEquals($groupId, $processedConfig['group_id']);
    }

    public function testEnableAutoOffsetStoreConfig(): void
    {
        $processedConfig = $this->getProcessedConfig(['enable_auto_offset_store' => false]);

        $this->assertFalse($processedConfig['enable_auto_offset_store']);
    }

    public function testEnableAutoCommitConfig(): void
    {
        $processedConfig = $this->getProcessedConfig(['enable_auto_commit' => false]);

        $this->assertFalse($processedConfig['enable_auto_commit']);
    }

    private function getProcessedConfig(array $rawConfig): array
    {
        $configuration = new Configuration();

        return $this->processor->processConfiguration($configuration, [$rawConfig]);
    }
}
