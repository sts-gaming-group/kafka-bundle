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
        $treeBuilder
            ->getRootNode()
                ->ignoreExtraKeys(false)
            ->end();

        return $treeBuilder;
    }
}
