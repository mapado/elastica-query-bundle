<?php

namespace Mapado\ElasticaQueryBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MapadoElasticaQueryExtension extends Extension
{
    /**
     * clients
     *
     * @var array
     * @access private
     */
    private $clients;

    /**
     * types
     *
     * @var array
     * @access private
     */
    private $types;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (empty($config['clients']) || empty($config['indexes'])) {
            // No Clients or indexes are defined
            return;
        }

        $this->treatClientSection($config['clients'], $container);
        $this->treatIndexSection($config['indexes'], $container);
        $this->treatDocumentManagerSection($config['document_managers'], $container);
    }

    /**
     * treatClientSection
     *
     * @param array $clients
     * @param ContainerBuilder $container
     * @access private
     * @return void
     */
    private function treatClientSection(array $clients, ContainerBuilder $container)
    {
        // manage clients
        foreach ($clients as $clientName => $client) {
            $clientServiceId = sprintf('mapado.elastica.client.%s', $clientName);
            $clientService = new Definition('Mapado\ElasticaQueryBundle\Client');
            $clientService->addArgument(
                [
                    'host' => $client['host'],
                    'port' => $client['port'],
                    'timeout' => $client['timeout'],
                ]
            );
            if ($container->getParameter('kernel.debug')) {
                $clientService->addMethodCall(
                    'setStopwatch',
                    [new Reference('debug.stopwatch', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]
                );
                $clientService->addMethodCall(
                    'setDataCollector',
                    [new Reference('mapado.elastica.data_collector')]
                );
            }
            $clientRef = new Reference($clientServiceId);
            $this->clients[$clientName] = $clientRef;
            $container->setDefinition($clientServiceId, $clientService)
                ->setFactoryService($clientRef);
        }
    }

    /**
     * treatIndexSection
     *
     * @param array $indexes
     * @param ContainerBuilder $container
     * @access private
     * @return void
     */
    private function treatIndexSection(array $indexes, ContainerBuilder $container)
    {
        // manage index and types
        foreach ($indexes as $indexName => $index) {
            // register the index
            $indexServiceId = sprintf('mapado.elastica.index.%s', $indexName);
            $indexService = new Definition('Elastica\Index', [$indexName]);
            $container->setDefinition($indexServiceId, $indexService)
                ->setFactoryService($this->clients[$index['client']])
                ->setFactoryMethod('getIndex');

            // register the types
            $indexRef = new Reference($indexServiceId);

            if ($index['types']) {
                $typeList = array_keys($index['types']);
                foreach ($typeList as $typeName) {
                    $typeServiceId = sprintf('mapado.elastica.type.%s.%s', $indexName, $typeName);
                    $typeService = new Definition('Elastica\Type', [$typeName]);

                    $this->types[$typeServiceId] = new Reference($typeServiceId);

                    $container->setDefinition($typeServiceId, $typeService)
                        ->setFactoryService($indexRef)
                        ->setFactoryMethod('getType');
                }
            }
        }
    }

    /**
     * treatDocumentManagerSection
     *
     * @param array $documentManagers
     * @param ContainerBuilder $container
     * @access private
     * @return void
     */
    private function treatDocumentManagerSection(array $documentManagers, ContainerBuilder $container)
    {
        $serviceList = [];
        foreach ($documentManagers as $name => $documentManager) {
            $serviceId = sprintf('mapado.elastica.document_manager.%s', $name);
            $serviceList[$name] = $serviceId;

            $service = new Definition(
                'Mapado\ElasticaQueryBundle\DocumentManager',
                [
                    $this->types[$documentManager['type']],
                    new Definition('Doctrine\Common\EventManager')
                ]
            );

            $definition = $container->setDefinition($serviceId, $service);

            if (!empty($documentManager['data_transformer'])) {
                $definition->addMethodCall(
                    'setDataTransformer',
                    [new Reference($documentManager['data_transformer'])]
                );
            }
        }

        if ($serviceList) {
            $container->setParameter('mapado.elastica.document_managers', $serviceList);
        }
    }
}
