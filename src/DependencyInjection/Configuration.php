<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfony_health_check');
        $root = $treeBuilder->getRootNode()->children();
    
        $this->addHealthCheckSection($root);

        return $treeBuilder;
    }
    
    private function addHealthCheckSection(NodeBuilder $builder): void
    {
        $builder
            ->arrayNode('health_checks')
                ->prototype('array')
                    ->children()
                        ->scalarNode('id')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->canBeEnabled()
            ->end()
        ;
    }
}
