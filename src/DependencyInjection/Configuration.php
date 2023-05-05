<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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
        $rootChildren = $rootNode->children();
        $rootChildren->booleanNode('is_cache_enabled')->defaultValue(true);
        $rootChildren->booleanNode('default_event_cache_enabled')->defaultValue(true);
        $rootChildren->booleanNode('default_event_block_cache_enabled')->defaultValue(true);
        $rootChildren->scalarNode('default_event_cache_ttl')->defaultValue(86400);
        $rootChildren->scalarNode('default_event_block_cache_ttl')->defaultValue(86400);

        $events = $rootChildren->arrayNode('cacheable_sylius_template_events')->arrayPrototype()->children();

        $events->scalarNode('name');
        $events->scalarNode('ttl');
        $events->booleanNode('is_cache_enabled');
        $events->booleanNode('default_event_block_cache_enabled')->defaultValue(true);
        $events->scalarNode('default_event_block_cache_ttl')->defaultValue(86400);

        $blocks = $events->arrayNode('blocks')->arrayPrototype()->children();

        $blocks->scalarNode('name');
        $blocks->scalarNode('ttl');
        $blocks->booleanNode('is_cache_enabled');

        /** @var NodeBuilder $events */
        $events = $blocks->end()->end();

        /** @var NodeBuilder $rootChildren */
        $rootChildren = $events->end()->end();

        return $treeBuilder;
    }
}
