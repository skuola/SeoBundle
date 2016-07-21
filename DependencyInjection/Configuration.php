<?php

namespace OpenSkuola\SeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('skuola_seo');

        $rootNode
            ->children()
                ->scalarNode('domain')
                    ->defaultValue('%domain%')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
