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

        $events = $rootChildren->arrayNode('template_events')->arrayPrototype()->children();

        $events->scalarNode('name');
        $events->scalarNode('ttl')->defaultValue(0);
        $events->scalarNode('block_default_ttl')->defaultValue(0);

        $blocks = $events->arrayNode('blocks')->arrayPrototype()->children();

        $blocks->scalarNode('name');
        $blocks->scalarNode('ttl');

        /** @var NodeBuilder $events */
        $events = $blocks->end()->end();

        /** @var NodeBuilder $rootChildren */
        $rootChildren = $events->end()->end();

        return $treeBuilder;
    }
}
