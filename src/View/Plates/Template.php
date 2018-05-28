<?php

namespace Obullo\Mvc\View\Plates;

use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerProxyTrait,
    ContainerAwareInterface
};
use League\Plates\Template\Template as PlatesTemplate;

/**
 * Plates template engine - http://platesphp.com/
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Template extends PlatesTemplate implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ContainerProxyTrait;

    /**
     * Container proxy:
     * Provides access to container variables from template files
     *
     * @param string $key key
     *
     * @return object Controller
     */
    public function __get(string $key)
    {
        return $this->container->get($key);
    }
}