<?php namespace buildr\tests\serviceProvider\dummy;

use buildr\ServiceProvider\ServiceProviderInterface;

/**
 * Dummy test class
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage test\serviceProvider\dummy
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class dummyProviderTwo implements ServiceProviderInterface {

    /**
     * Returns an object that be registered to registry
     *
     * @return Object
     */
    public function register() {
        return new \stdClass();
    }

    /**
     * Returns the binding name in the registry
     *
     * @return string
     */
    public function getBindingName() {
        return "dummyTwo";
    }

    /**
     * Return an array that contains interface bindings that
     * registered along with the provider.
     *
     * @return NULL|array
     */
    public function provides() {
        return [
            ServiceProviderInterface::class,
        ];
    }

}
