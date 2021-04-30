<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfony_health_check');
        $root = $treeBuilder->getRootNode();
        $root
            ->children()
                ->arrayNode('health_checks')
                    ->children()
                        ->scalarNode('id')->cannotBeEmpty()->end()
                    ->end()
                ->canBeEnabled()
                ->end()
            ->end()
        ;
    
        return $treeBuilder;
    }
}
