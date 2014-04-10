<?php

namespace Jma\ResourceBundle\DependencyInjection\Driver;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Driver\DoctrineORMDriver;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Description of JmaDoctrineORMDriver
 *
 * @author maarek
 */
class JmaDoctrineORMDriver extends DoctrineORMDriver
{

    /**
     * @return Definition
     */
    protected function getConfigurationDefinition()
    {
        $definition = new Definition('Jma\ResourceBundle\Controller\Configuration');
        $definition
                ->setFactoryService('jma_resource.controller.configuration_factory')
                ->setFactoryMethod('createConfiguration')
                ->setArguments(array($this->prefix, $this->resourceName, $this->templates))
                ->setPublic(false)
        ;

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepositoryDefinition(array $classes)
    {
        $definition = parent::getRepositoryDefinition($classes);
        $implements = class_implements($definition->getClass());
        if (isset($implements['Symfony\Component\DependencyInjection\ContainerAwareInterface'])) {
            $definition->addMethodCall('setContainer', array(new Reference('service_container')));
        }

        return $definition;
    }
}
