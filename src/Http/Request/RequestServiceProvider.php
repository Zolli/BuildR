<?php namespace buildr\Http\Request;

use buildr\Container\ContainerInterface;
use buildr\Http\Request\RequestInterface;
use buildr\ServiceProvider\ServiceProviderInterface;

/**
 * HTTP Request service provider
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Http\Request
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class RequestServiceProvider implements ServiceProviderInterface {

    /**
     * Returns an object that be registered to registry
     *
     * @param \buildr\Container\ContainerInterface $container
     *
     * @return Object
     */
    public function register(ContainerInterface $container) {
        $request = new Request();
        $request->createFromGlobals($_SERVER, $_COOKIE, $_GET, $_POST, $_FILES);

        return $request;
    }

    /**
     * Return an array that contains interface bindings that
     * registered along with the provider.
     *
     * @return NULL|array
     */
    public function provides() {
        return [
            RequestInterface::class,
        ];
    }

    /**
     * Returns the binding name in the registry
     *
     * @return string
     */
    public function getBindingName() {
        return 'request';
    }


}
