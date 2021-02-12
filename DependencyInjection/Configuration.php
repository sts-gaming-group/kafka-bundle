<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sts_kafka');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('brokers')
                    ->scalarPrototype()
                        ->defaultValue('[127.0.0.1]')
                    ->end()
                ->end()
                ->scalarNode('schema_registry')
                    ->defaultValue('127.0.0.1')
                ->end()
                ->scalarNode('group_id')
                    ->defaultValue('sts_kafka')
                ->end()
                ->booleanNode('enable_auto_offset_store')
                    ->defaultValue(true)
                ->end()
                ->booleanNode('enable_auto_commit')
                    ->defaultValue(true)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
