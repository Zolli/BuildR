<?php namespace buildr\Event;

use buildr\ServiceProvider\ServiceProviderInterface;
use buildr\Container\ContainerInterface;

/**
 * Event service provider
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Event
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class EventServiceProvider implements ServiceProviderInterface {

    /**
     * Returns an object that be registered to registry
     *
     * @param \buildr\Container\ContainerInterface $container
     *
     * @return Object
     */
    public function register(ContainerInterface $container) {
        return new Event();
    }

    /**
     * Return an array that contains interface bindings that
     * registered along with the provider.
     *
     * @return NULL|array
     */
    public function provides() {
        return NULL;
    }

    /**
     * Returns the binding name in the registry
     *
     * @return string
     */
    public function getBindingName() {
        return 'event';
    }


}
