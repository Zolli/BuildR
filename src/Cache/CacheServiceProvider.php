<?php namespace buildr\Cache;

use buildr\Config\Config;
use buildr\Cache\CacheDriverInterface;
use buildr\ServiceProvider\ServiceProviderInterface;
use buildr\Container\ContainerInterface;

/**
 * Cache service provider
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Cache
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 *
 * @codeCoverageIgnore
 */
class CacheServiceProvider implements ServiceProviderInterface {

    /**
     * Returns an object that be registered to registry
     *
     * @param \buildr\Container\ContainerInterface $container
     *
     * @return Object
     */
    public function register(ContainerInterface $container) {
        $driverName = Config::getMainConfig()['cache']['driver'];
        $driverClass = new $driverName;

        if(!($driverClass instanceof CacheDriverInterface)) {
            throw new \InvalidArgumentException("The cache driver ({$driverName}) not implements the CacheInterface!");
        }

        return $driverClass;

    }

    /**
     * Return an array that contains interface bindings that
     * registered along with the provider.
     *
     * @return NULL|array
     */
    public function provides() {
        return [
            CacheDriverInterface::class,
        ];
    }

    /**
     * Returns the binding name in the registry
     *
     * @return string
     */
    public function getBindingName() {
        return 'cache';
    }
}
