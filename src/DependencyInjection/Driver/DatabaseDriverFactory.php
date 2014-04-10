<?php

namespace Jma\ResourceBundle\DependencyInjection\Driver;

use Sylius\Bundle\ResourceBundle\Exception\Driver\UnknownDriverException;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Jma\ResourceBundle\JmaResourceBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Driver\DoctrineORMDriver;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Driver\DoctrineODMDriver;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Driver\DoctrinePHPCRDriver;

/**
 * @author Arnaud Langlade <aRn0D.dev@gmail.com>
 */
class DatabaseDriverFactory
{

    public static function get(
        $type
        , ContainerBuilder $container
        , $prefix
        , $resourceName
        , $templates = null
    )
    {
        switch ($type) {
            case SyliusResourceBundle::DRIVER_DOCTRINE_ORM:
                return new DoctrineORMDriver($container, $prefix, $resourceName, $templates);
            case SyliusResourceBundle::DRIVER_DOCTRINE_MONGODB_ODM:
                return new DoctrineODMDriver($container, $prefix, $resourceName, $templates);
            case SyliusResourceBundle::DRIVER_DOCTRINE_PHPCR_ODM:
                return new DoctrinePHPCRDriver($container, $prefix, $resourceName, $templates);
            case JmaResourceBundle::DRIVER_JMA_DOCTRINE_ORM:
                return new JmaDoctrineORMDriver($container, $prefix, $resourceName, $templates);
            default:
                throw new UnknownDriverException($type);
        }
    }

}
