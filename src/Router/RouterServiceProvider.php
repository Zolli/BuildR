<?php namespace buildr\Router;

use buildr\Router\RouterInterface;
use buildr\ServiceProvider\ServiceProviderInterface;
use buildr\Container\ContainerInterface;

/**
 * Router Service Provider
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Router
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 *
 * @codeCoverageIgnore
 */
class RouterServiceProvider implements ServiceProviderInterface {

    /**
     * Returns an object that be registered to registry
     *
     * @param \buildr\Container\ContainerInterface $container
     *
     * @return Object
     */
    public function register(ContainerInterface $container) {
        return new Router();
    }

    /**
     * Return an array that contains interface bindings that
     * registered along with the provider.
     *
     * @return NULL|array
     */
    public function provides() {
        return [
            RouterInterface::class,
        ];
    }

    /**
     * Returns the binding name in the registry
     *
     * @return string
     */
    public function getBindingName() {
        return 'router';
    }

}
