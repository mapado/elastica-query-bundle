<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mapado_elastica_query');

        $this->addClientsSection($rootNode);
        $this->addIndexesSection($rootNode);
        $this->addDocumentManagersSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Returns the array node used for "types".
     */
    protected function getTypesNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('types');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->treatNullLike([])
            ->end()
        ;

        return $node;
    }

    /**
     * Adds the configuration for the "clients" key
     */
    private function addClientsSection($rootNode)
    {
        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->arrayNode('clients')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('type')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('url')
                                ->validate()
                                    ->ifTrue(function ($url) {
                                        return $url && '/' !== substr($url, -1);
                                    })
                                    ->then(function ($url) {
                                        return $url . '/';
                                    })
                                ->end()
                            ->end()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('timeout')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addIndexesSection($rootNode)
    {
        $rootNode
            ->fixXmlConfig('index')
            ->children()
                ->arrayNode('indexes')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('index_name')
                                ->info('Defaults to the name of the index, but can be modified if the index name is different in ElasticSearch')
                            ->end()
                            ->scalarNode('client')->end()
                        ->end()
                        ->append($this->getTypesNode())
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addDocumentManagersSection($rootNode)
    {
        $rootNode
            ->fixXmlConfig('document_manager')
            ->children()
                ->arrayNode('document_managers')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('mapado elastica type reference (ex: `mapado.elastica.type.twitter.tweet`)')
                            ->end()
                            ->scalarNode('query_builder_classname')
                                ->info('mapado elastica query builder classname (if you want to override the default QueryBuilder)')
                            ->end()
                            ->scalarNode('data_transformer')
                                ->info('data transformer name. Reference to a instance of Mapado\ElasticaQueryBundle\DataTransformer\DataTransformerInterface')
                            ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
