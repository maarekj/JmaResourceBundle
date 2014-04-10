<?php

namespace Jma\ResourceBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ConfigurationFactory as BaseConfigurationFactory;
use Sylius\Bundle\ResourceBundle\Controller\ParametersParser;

class ConfigurationFactory extends BaseConfigurationFactory
{
    /**
     * Create configuration for given parameters.
     *
     * @param string $bundlePrefix
     * @param string $resourceName
     * @param string $templateNamespace
     * @param string $templatingEngine
     *
     * @return Configuration
     */
    public function createConfiguration($bundlePrefix, $resourceName, $templateNamespace, $templatingEngine = 'twig')
    {
        return new Configuration(
                $this->parametersParser, $bundlePrefix, $resourceName, $templateNamespace, $templatingEngine
        );
    }

}
