<?php

namespace enhavo\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('enhavo_admin');

        $rootNode
            ->children()
                ->booleanNode('permission_check')
                    ->defaultTrue()
                ->end()
            ->end()

            ->children()
                ->arrayNode('stylesheets')
                    ->prototype('scalar')->end()
                ->end()
            ->end()

            ->children()
                ->arrayNode('javascripts')
                    ->prototype('scalar')->end()
                ->end()
            ->end()

            ->children()
                ->arrayNode('menu')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')->isRequired()->end()
                            ->scalarNode('route')->isRequired()->end()
                            ->scalarNode('role')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->children()
                ->arrayNode('viewer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('app')->defaultValue('enhavo\AdminBundle\Viewer\AppViewer')->end()
                        ->scalarNode('create')->defaultValue('enhavo\AdminBundle\Viewer\CreateViewer')->end()
                        ->scalarNode('table')->defaultValue('enhavo\AdminBundle\Viewer\TableViewer')->end()
                        ->scalarNode('edit')->defaultValue('enhavo\AdminBundle\Viewer\EditViewer')->end()
                        ->scalarNode('index')->defaultValue('enhavo\AdminBundle\Viewer\IndexViewer')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
