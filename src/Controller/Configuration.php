<?php

namespace Jma\ResourceBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\Configuration as BaseConfiguration;

/**
 * Description of Configuration
 *
 * @author Maarek Joseph
 */
class Configuration extends BaseConfiguration
{

    public function getFilterType()
    {
        return $this->get('filter', sprintf('%s_%s_filter', $this->bundlePrefix, $this->resourceName));
    }

}
