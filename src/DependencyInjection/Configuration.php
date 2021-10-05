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
        $builder
            ->append($this->addConsumersNode())
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
                ->defaultValue((new ProducerPartition())->getDefaultValue())
            ->end()
            ->scalarNode(ProducerTopic::NAME)
                ->defaultValue((new ProducerTopic())->getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->integerNode(LogLevel::NAME)
                ->defaultValue((new LogLevel)->getDefaultValue())
            ->end()
            ->arrayNode(Brokers::NAME)
                ->defaultValue((new Brokers)->getDefaultValue())
                ->cannotBeEmpty()
                    ->scalarPrototype()
                    ->cannotBeEmpty()
            ->end();
    }

    private function addConsumerConfigurations(NodeBuilder $builder): void
    {
        $builder
            ->scalarNode(SchemaRegistry::NAME)
                ->defaultValue((new SchemaRegistry)->getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->arrayNode(Validators::NAME)
                ->defaultValue((new Validators)->getDefaultValue())
                ->cannotBeEmpty()
                    ->scalarPrototype()
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->arrayNode(Topics::NAME)
                ->defaultValue((new Topics)->getDefaultValue())
                ->cannotBeEmpty()
                    ->scalarPrototype()
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->scalarNode(Decoder::NAME)
                ->defaultValue((new Decoder)->getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(Denormalizer::NAME)
                ->defaultValue((new Denormalizer)->getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(AutoCommitIntervalMs::NAME)
                ->defaultValue((new AutoCommitIntervalMs)->getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(AutoOffsetReset::NAME)
                ->defaultValue((new AutoOffsetReset)->getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(GroupId::NAME)
                ->defaultValue((new GroupId)->getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->integerNode(Timeout::NAME)
                ->defaultValue((new Timeout)->getDefaultValue())
            ->end()
            ->scalarNode(EnableAutoOffsetStore::NAME)
                ->defaultValue((new EnableAutoOffsetStore)->getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(EnableAutoCommit::NAME)
                ->defaultValue((new EnableAutoCommit)->getDefaultValue())
                ->cannotBeEmpty()
            ->end()
            ->booleanNode(RegisterMissingSchemas::NAME)
                ->defaultValue((new RegisterMissingSchemas)->getDefaultValue())
            ->end()
            ->booleanNode(RegisterMissingSubjects::NAME)
                ->defaultValue((new RegisterMissingSubjects)->getDefaultValue())
            ->end()
            ->integerNode(MaxRetries::NAME)
                ->defaultValue((new MaxRetries)->getDefaultValue())
            ->end()
            ->integerNode(RetryDelay::NAME)
                ->defaultValue((new RetryDelay)->getDefaultValue())
            ->end()
            ->integerNode(RetryMultiplier::NAME)
                ->defaultValue((new RetryMultiplier)->getDefaultValue())
            ->end()
            ->integerNode(MaxRetryDelay::NAME)
                ->defaultValue((new MaxRetryDelay)->getDefaultValue())
            ->end()
            ->integerNode(LogLevel::NAME)
                ->defaultValue((new LogLevel)->getDefaultValue())
            ->end()
            ->arrayNode(Brokers::NAME)
                ->defaultValue((new Brokers)->getDefaultValue())
                ->cannotBeEmpty()
                    ->scalarPrototype()
                    ->cannotBeEmpty()
            ->end();
    }
}
