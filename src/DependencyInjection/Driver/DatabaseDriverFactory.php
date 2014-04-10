<?php

namespace Jma\ResourceBundle\DependencyInjection\Driver;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Driver\DatabaseDriverFactory as BaseDatabaseDriverFactory;
use Sylius\Bundle\ResourceBundle\Exception\Driver\UnknownDriverException;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Jma\ResourceBundle\JmaResourceBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Joseph Maarek <josephmaarek@gmail.com>
 */
class DatabaseDriverFactory extends BaseDatabaseDriverFactory
{
    public static function get(
        $type = SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        ContainerBuilder $container,
        $prefix,
        $resourceName,
        $templates = null
    )
    {
        try {
            return parent::get($type, $container, $prefix, $resourceName, $templates);
        } catch (UnknownDriverException $e) {
            switch ($type) {
                case JmaResourceBundle::DRIVER_JMA_DOCTRINE_ORM:
                    return new JmaDoctrineORMDriver($container, $prefix, $resourceName, $templates);
                default:
                    throw new UnknownDriverException($type);
            }
        }
    }
}
