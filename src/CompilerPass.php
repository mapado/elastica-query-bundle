<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
{
    /**
     * process
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('mapado.elastica.document_managers')) {
            return;
        }

        $serviceList = $container->getParameter('mapado.elastica.document_managers');
        $taggedServices = $container->findTaggedServiceIds('mapado_elastica_query_builder.event_listener');

        foreach ($serviceList as $service) {
            $definition = $container->getDefinition($service);

            foreach ($taggedServices as $id => $attributes) {
                foreach ($attributes as $attribute) {
                    $definition->addMethodCall(
                        'addEventListener',
                        [$attribute['event'], new Reference($id)]
                    );
                }
            }
        }
    }
}
