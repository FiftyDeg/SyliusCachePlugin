<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fifty_deg_sylius_cache');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->booleanNode('is_cache_enabled')
                    ->defaultValue(true)
                ->end()
                ->arrayNode('cacheable_sylius_template_events')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                            ->end()
                            ->scalarNode('ttl')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}