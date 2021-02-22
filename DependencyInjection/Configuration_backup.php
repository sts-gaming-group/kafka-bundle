<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration_backup implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sts_kafka');
        $rootNode = $treeBuilder->getRootNode();

        $builder = $rootNode->children();
        $this->addConfigurations($builder);
        $builder->append($this->addConsumersSection());

        return $treeBuilder;
    }

    private function addConsumersSection()
    {
        $treeBuilder = new TreeBuilder('consumers');

        $node = $treeBuilder->getRootNode();
        $builder = $node->arrayPrototype()->children();
        $this->addConfigurations($builder);
        $builder->end();

        return $node;
    }

    private function addConfigurations(NodeBuilder $builder)
    {
        return $builder
            ->booleanNode('dry-run')
            ->defaultFalse()
            ->end()
            ->scalarNode('group_id')
            ->cannotBeEmpty()
            ->defaultValue('')
            ->cannotBeEmpty()
            ->end()
            ->arrayNode('topics')
            ->scalarPrototype()
            ->defaultValue('')
            ->cannotBeEmpty()
            ->end()
            ->end()
            ->arrayNode('brokers')
            ->scalarPrototype()
            ->defaultValue('')
            ->cannotBeEmpty()
            ->end()
            ->end()
            ->scalarNode('schema_registry')
            ->defaultValue('')
            ->cannotBeEmpty()
            ->end()
            ->booleanNode('enable_auto_offset_store')
            ->defaultValue(true)->end()
            ->booleanNode('enable_auto_commit')
            ->defaultValue(true)->end()
            ->scalarNode('offset_store_method')
            ->defaultValue('broker')->end()
            ->end()
            ->ignoreExtraKeys(false);
    }
}
