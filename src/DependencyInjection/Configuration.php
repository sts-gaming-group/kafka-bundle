<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\DependencyInjection;

use Sts\KafkaBundle\Configuration\Type\AutoCommitIntervalMs;
use Sts\KafkaBundle\Configuration\Type\AutoOffsetReset;
use Sts\KafkaBundle\Configuration\Type\Brokers;
use Sts\KafkaBundle\Configuration\Type\Decoder;
use Sts\KafkaBundle\Configuration\Type\EnableAutoCommit;
use Sts\KafkaBundle\Configuration\Type\EnableAutoOffsetStore;
use Sts\KafkaBundle\Configuration\Type\GroupId;
use Sts\KafkaBundle\Configuration\Type\LogLevel;
use Sts\KafkaBundle\Configuration\Type\Offset;
use Sts\KafkaBundle\Configuration\Type\OffsetStoreMethod;
use Sts\KafkaBundle\Configuration\Type\Partition;
use Sts\KafkaBundle\Configuration\Type\RegisterMissingSchemas;
use Sts\KafkaBundle\Configuration\Type\RegisterMissingSubjects;
use Sts\KafkaBundle\Configuration\Type\SchemaRegistry;
use Sts\KafkaBundle\Configuration\Type\Timeout;
use Sts\KafkaBundle\Configuration\Type\Topics;
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
        $this->addConfigurations($builder);
        $builder->append($this->addConsumersSection())
            ->append($this->addProducersSection());

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addConsumersSection()
    {
        $treeBuilder = new TreeBuilder('consumers');

        $node = $treeBuilder->getRootNode();
        $builder = $node->arrayPrototype()->children();
        $this->addConfigurations($builder);
        $builder->end();

        return $node;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addProducersSection()
    {
        $treeBuilder = new TreeBuilder('producers');

        $node = $treeBuilder->getRootNode();
        $builder = $node->arrayPrototype()->children();
        $this->addConfigurations($builder);
        $builder->end();

        return $node;
    }

    /**
     * @param NodeBuilder $builder
     * @return mixed
     */
    private function addConfigurations(NodeBuilder $builder)
    {
        $types = $this->getTypes();

        return
            $builder
                ->scalarNode(AutoCommitIntervalMs::NAME)
                    ->defaultValue(AutoCommitIntervalMs::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[AutoCommitIntervalMs::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[AutoCommitIntervalMs::class]->getDescription())
                    ->end()
                ->end()
                ->scalarNode(AutoOffsetReset::NAME)
                    ->defaultValue(AutoOffsetReset::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[AutoOffsetReset::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[AutoOffsetReset::class]->getDescription())
                    ->end()
                ->end()
                ->arrayNode(Brokers::NAME)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[Brokers::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[Brokers::class]->getDescription())
                    ->end()
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode(Decoder::NAME)
                    ->defaultValue(Decoder::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[Decoder::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[Decoder::class]->getDescription())
                    ->end()
                ->end()
                ->scalarNode(GroupId::NAME)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[GroupId::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[GroupId::class]->getDescription())
                    ->end()
                ->end()
                ->integerNode(Offset::NAME)
                    ->defaultValue(Offset::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[Offset::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[Offset::class]->getDescription())
                    ->end()
                ->end()
                ->scalarNode(OffsetStoreMethod::NAME)
                    ->defaultValue(OffsetStoreMethod::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[OffsetStoreMethod::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[OffsetStoreMethod::class]->getDescription())
                    ->end()
                ->end()
                ->integerNode(Partition::NAME)
                    ->defaultValue(Partition::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[Partition::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[Partition::class]->getDescription())
                    ->end()
                ->end()
                ->scalarNode(SchemaRegistry::NAME)
                    ->defaultValue(SchemaRegistry::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[SchemaRegistry::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[SchemaRegistry::class]->getDescription())
                    ->end()
                ->end()
                ->integerNode(Timeout::NAME)
                    ->defaultValue(Timeout::DEFAULT_VALUE)
                        ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[Timeout::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[Timeout::class]->getDescription())
                    ->end()
                ->end()
                ->arrayNode(Topics::NAME)
                    ->defaultValue(Topics::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[Topics::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[Topics::class]->getDescription())
                    ->end()
                    ->scalarPrototype()->end()
                ->end()
                ->booleanNode(EnableAutoOffsetStore::NAME)
                    ->defaultValue(EnableAutoOffsetStore::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[EnableAutoOffsetStore::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[EnableAutoOffsetStore::class]->getDescription())
                    ->end()
                ->end()
                ->booleanNode(EnableAutoCommit::NAME)
                    ->defaultValue(EnableAutoCommit::DEFAULT_VALUE)
                    ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[EnableAutoCommit::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[EnableAutoCommit::class]->getDescription())
                    ->end()
                ->end()
                ->integerNode(LogLevel::NAME)
                    ->defaultValue(LogLevel::DEFAULT_VALUE)
                        ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[LogLevel::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[LogLevel::class]->getDescription())
                    ->end()
                ->end()
                ->booleanNode(RegisterMissingSchemas::NAME)
                    ->defaultValue(RegisterMissingSchemas::DEFAULT_VALUE)
                        ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[RegisterMissingSchemas::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[RegisterMissingSchemas::class]->getDescription())
                    ->end()
                ->end()
                ->booleanNode(RegisterMissingSubjects::NAME)
                    ->defaultValue(RegisterMissingSubjects::DEFAULT_VALUE)
                        ->validate()
                        ->ifTrue(function ($value) use ($types) {
                            return !$types[RegisterMissingSubjects::class]->isValueValid($value);
                        })
                        ->thenInvalid($types[RegisterMissingSubjects::class]->getDescription())
                    ->end()
                ->end()
            ->end();
    }

    private function getTypes(): array
    {
        return [
            AutoCommitIntervalMs::class => new AutoCommitIntervalMs(),
            AutoOffsetReset::class => new AutoOffsetReset(),
            Brokers::class => new Brokers(),
            Decoder::class => new Decoder(),
            GroupId::class => new GroupId(),
            Offset::class => new Offset(),
            OffsetStoreMethod::class => new OffsetStoreMethod(),
            Partition::class => new Partition(),
            SchemaRegistry::class => new SchemaRegistry(),
            Timeout::class => new Timeout(),
            Topics::class => new Topics(),
            EnableAutoOffsetStore::class => new EnableAutoOffsetStore(),
            EnableAutoCommit::class => new EnableAutoCommit(),
            LogLevel::class => new LogLevel(),
            RegisterMissingSchemas::class => new RegisterMissingSchemas(),
            RegisterMissingSubjects::class => new RegisterMissingSubjects()
        ];
    }
}
