<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\DependencyInjection;

use Sts\KafkaBundle\Configuration\Type\AutoCommitIntervalMs;
use Sts\KafkaBundle\Configuration\Type\AutoOffsetReset;
use Sts\KafkaBundle\Configuration\Type\Brokers;
use Sts\KafkaBundle\Configuration\Type\Decoder;
use Sts\KafkaBundle\Configuration\Type\Denormalizer;
use Sts\KafkaBundle\Configuration\Type\EnableAutoCommit;
use Sts\KafkaBundle\Configuration\Type\EnableAutoOffsetStore;
use Sts\KafkaBundle\Configuration\Type\EventEveryMessageCount;
use Sts\KafkaBundle\Configuration\Type\GroupId;
use Sts\KafkaBundle\Configuration\Type\LogLevel;
use Sts\KafkaBundle\Configuration\Type\MaxRetries;
use Sts\KafkaBundle\Configuration\Type\MaxRetryDelay;
use Sts\KafkaBundle\Configuration\Type\ProducerPartition;
use Sts\KafkaBundle\Configuration\Type\ProducerTopic;
use Sts\KafkaBundle\Configuration\Type\RegisterMissingSchemas;
use Sts\KafkaBundle\Configuration\Type\RegisterMissingSubjects;
use Sts\KafkaBundle\Configuration\Type\RetryDelay;
use Sts\KafkaBundle\Configuration\Type\RetryMultiplier;
use Sts\KafkaBundle\Configuration\Type\SchemaRegistry;
use Sts\KafkaBundle\Configuration\Type\Timeout;
use Sts\KafkaBundle\Configuration\Type\Topics;
use Sts\KafkaBundle\Configuration\Type\Validators;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sts_kafka');

        $rootNode = $treeBuilder->getRootNode();
        $builder = $rootNode->children();
        $builder->append($this->addConsumersNode())
            ->append($this->addProducersNode());

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addConsumersNode()
    {
        $consumersTreeBuilder = new TreeBuilder('consumers');
        $consumersNode = $consumersTreeBuilder->getRootNode();
        $consumersBuilder = $consumersNode->children();

        $instancesTreeBuilder = new TreeBuilder('instances');
        $instancesNode = $instancesTreeBuilder->getRootNode();
        $instancesBuilder = $instancesNode->arrayPrototype()->children();
        $this->addConsumerConfigurations($instancesBuilder);

        $consumersBuilder->append($instancesNode);

        return $consumersNode;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addProducersNode()
    {
        $producersTreeBuilder = new TreeBuilder('producers');
        $producersNode = $producersTreeBuilder->getRootNode();
        $producersBuilder = $producersNode->children();

        $instancesTreeBuilder = new TreeBuilder('instances');
        $instancesNode = $instancesTreeBuilder->getRootNode();
        $instancesBuilder = $instancesNode->arrayPrototype()->children();
        $this->addProducerConfigurations($instancesBuilder);

        $producersBuilder->append($instancesNode);

        return $producersNode;
    }

    private function addProducerConfigurations(NodeBuilder $builder): void
    {
         $builder
            ->integerNode(ProducerPartition::NAME)
                ->defaultValue(ProducerPartition::getDefaultValue())
            ->end()
            ->scalarNode(ProducerTopic::NAME)
                ->defaultValue(ProducerTopic::getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->integerNode(LogLevel::NAME)
                ->defaultValue(LogLevel::getDefaultValue())
            ->end()
            ->arrayNode(Brokers::NAME)
                ->defaultValue(Brokers::getDefaultValue())
                ->cannotBeEmpty()
                    ->scalarPrototype()
                    ->cannotBeEmpty()
            ->end();
    }

    private function addConsumerConfigurations(NodeBuilder $builder): void
    {
        $builder
            ->scalarNode(SchemaRegistry::NAME)
                ->defaultValue(SchemaRegistry::getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->arrayNode(Validators::NAME)
                ->defaultValue(Validators::getDefaultValue())
                ->cannotBeEmpty()
                    ->scalarPrototype()
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->arrayNode(Topics::NAME)
                ->defaultValue(Topics::getDefaultValue())
                ->cannotBeEmpty()
                    ->scalarPrototype()
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->scalarNode(Decoder::NAME)
                ->defaultValue(Decoder::getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(Denormalizer::NAME)
                ->defaultValue(Denormalizer::getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(AutoCommitIntervalMs::NAME)
                ->defaultValue(AutoCommitIntervalMs::getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(AutoOffsetReset::NAME)
                ->defaultValue(AutoOffsetReset::getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(GroupId::NAME)
                ->defaultValue(GroupId::getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->integerNode(Timeout::NAME)
                ->defaultValue(Timeout::getDefaultValue())
            ->end()
            ->scalarNode(EnableAutoOffsetStore::NAME)
                ->defaultValue(EnableAutoOffsetStore::getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(EnableAutoCommit::NAME)
                ->defaultValue(EnableAutoCommit::getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->booleanNode(RegisterMissingSchemas::NAME)
                ->defaultValue(RegisterMissingSchemas::getDefaultValue())
            ->end()
            ->booleanNode(RegisterMissingSubjects::NAME)
                ->defaultValue(RegisterMissingSubjects::getDefaultValue())
            ->end()
            ->integerNode(MaxRetries::NAME)
                ->defaultValue(MaxRetries::getDefaultValue())
            ->end()
            ->integerNode(RetryDelay::NAME)
                ->defaultValue(RetryDelay::getDefaultValue())
            ->end()
            ->integerNode(RetryMultiplier::NAME)
                ->defaultValue(RetryMultiplier::getDefaultValue())
            ->end()
            ->integerNode(MaxRetryDelay::NAME)
                ->defaultValue(MaxRetryDelay::getDefaultValue())
            ->end()
            ->integerNode(LogLevel::NAME)
                ->defaultValue(LogLevel::getDefaultValue())
            ->end()
            ->arrayNode(Brokers::NAME)
                ->defaultValue(Brokers::getDefaultValue())
                ->cannotBeEmpty()
                    ->scalarPrototype()
                    ->cannotBeEmpty()
            ->end();
    }
}
