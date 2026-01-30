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

        $root = $treeBuilder->getRootNode();

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
                ->variableNode('redis_dsn')
                    ->defaultValue(null)
                    ->validate()
                        ->ifTrue(function ($value) {
                            return $value !== null && !is_string($value);
                        })
                        ->thenInvalid('The redis_dsn must be a string or null.')
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
                ->arrayNode('rate_limiter')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('health')
                            ->canBeEnabled()
                            ->children()
                                ->enumNode('policy')
                                    ->values(['fixed_window', 'sliding_window', 'token_bucket'])
                                    ->defaultValue('fixed_window')
                                ->end()
                                ->integerNode('limit')
                                    ->defaultValue(100)
                                    ->min(1)
                                ->end()
                                ->scalarNode('interval')
                                    ->defaultValue('60 minutes')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('ping')
                            ->canBeEnabled()
                            ->children()
                                ->enumNode('policy')
                                    ->values(['fixed_window', 'sliding_window', 'token_bucket'])
                                    ->defaultValue('fixed_window')
                                ->end()
                                ->integerNode('limit')
                                    ->defaultValue(100)
                                    ->min(1)
                                ->end()
                                ->scalarNode('interval')
                                    ->defaultValue('60 minutes')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
