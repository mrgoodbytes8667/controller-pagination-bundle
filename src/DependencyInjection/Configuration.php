<?php


namespace Bytes\ControllerPaginationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('bytes_controller_pagination');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('offsets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('begin')
                            ->min(1)
                            ->defaultValue(1)
                            ->setDeprecated('mrgoodbytes8667/controller-pagination-bundle', '0.2.0', 'The child node "%node%" at path "%path%" is deprecated. It has been replaced by "start".')
                        ->end()
                        ->integerNode('start')
                            ->min(1)
                            //->defaultValue(1)
                        ->end()
                        ->integerNode('end')
                            ->min(1)
                            ->defaultValue(1)
                        ->end()
                        ->integerNode('current')
                            ->min(1)
                            ->defaultValue(1)
                        ->end()
                    ->end() // end children (offset)
                ->end() // end offset
                ->arrayNode('parameters_allowlist')
                    ->scalarPrototype()->end()
                ->end() // end parameter allowlist
                ->arrayNode('parameter_allowlist')
                    ->scalarPrototype()->end()
                    ->setDeprecated('mrgoodbytes8667/controller-pagination-bundle', '0.2.0', 'The child node "%node%" at path "%path%" is deprecated. It has been replaced by "parameters_allowlist".')
                ->end() // end parameter allowlist
            ->end();

        return $treeBuilder;
    }
}
