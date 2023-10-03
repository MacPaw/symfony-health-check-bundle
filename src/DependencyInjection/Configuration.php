<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Response;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfony_health_check');

        /** @var ArrayNodeDefinition $root */
        $root = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('symfony_health_check');

        $root
            ->children()
                ->variableNode('ping_error_response_code')
                    ->defaultValue(null)
                    ->validate()
                        ->ifTrue(function ($value) {
                            return $value !== null && !array_key_exists($value, Response::$statusTexts);
                        })
                        ->thenInvalid('The ping_error_response_code must be valid HTTP status code or null.')
                    ->end()
                ->end()
                ->variableNode('health_error_response_code')
                    ->defaultValue(null)
                    ->validate()
                        ->ifTrue(function ($value) {
                            return $value !== null && !array_key_exists($value, Response::$statusTexts);
                        })
                        ->thenInvalid('The health_error_response_code must be valid HTTP status code or null.')
                    ->end()
                ->end()
                ->arrayNode('health_checks')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('ping_checks')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
