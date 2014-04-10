<?php

namespace Jma\ResourceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Jma\ResourceBundle\DependencyInjection\Driver\DatabaseDriverFactory;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class JmaResourceExtension extends Extension
{

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $classes = isset($config['resources']) ? $config['resources'] : array();

        $this->createResourceServices($classes, $container);

        if ($container->hasParameter('jma_resource.config.classes')) {
            $classes = array_merge($classes, $container->getParameter('jma_resource.config.classes'));
        }

        $container->setParameter('jma_resource.config.classes', $classes);
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function createResourceServices(array $configs, ContainerBuilder $container)
    {
        foreach ($configs as $name => $config) {
            list($prefix, $resourceName) = explode('.', $name);

            DatabaseDriverFactory::get(
                    $config['driver']
                    , $container
                    , $prefix
                    , $resourceName
                    , array_key_exists('templates', $config) ? $config['templates'] : null
            )->load($config['classes']);
        }
    }

}
