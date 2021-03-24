<?php

    namespace App\Application\Articul\SearchBundle\DependencyInjection;


    use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    class Configuration implements ConfigurationInterface
    {
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();

            $rootNode = $treeBuilder->root('articul_search', 'array');

            $rootNode->children()
                ->arrayNode('routing')
                    ->children()
                        ->scalarNode('prefix')
                    ->end()
                ->end();
            $rootNode->children()
                    ->arrayNode('mapping')
                        ->children()
                            ->arrayNode('types')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                            ->append($this->getPropertiesNode())
                            ->children()
                                ->scalarNode('model')->end()
                                ->scalarNode('title')->end()
                                ->scalarNode('entityName')->end()
                                ->scalarNode('body')->end()
                                ->arrayNode('filters')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                                ->arrayNode('route')
                                    ->children()
                                        ->scalarNode('name')->info('route')->end()
                                        ->scalarNode('entity_property')->defaultNull()->end()
                                        ->arrayNode('parameters')
                                            ->useAttributeAsKey('name')
                                            ->prototype('scalar')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
            return $treeBuilder;
        }

        /**
         * Returns the array node used for "properties".
         */
        protected function getPropertiesNode()
        {
            $builder = new TreeBuilder();
            $node = $builder->root('properties');

            $node
                ->useAttributeAsKey('name')
                ->prototype('variable')
                ->treatNullLike([]);

            return $node;
        }
    }