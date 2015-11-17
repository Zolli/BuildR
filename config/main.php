<?php

use buildr\Logger\LoggerServiceProvider;
use buildr\Config\ConfigServiceProvider;
use buildr\Filesystem\FilesystemServiceProvider;
use buildr\Cache\CacheServiceProvider;

/**
 * Main configuration file
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
return [

    /**
     * Application Configuration
     */
    'application' => [
        'location'          => '/application',
        'namespaceName'     => 'buildr\\Application\\'
    ],

    /**
     * Startup configuration
     */
    'startup' => [
        'detector' => 'buildr\Startup\Environment\Detector\HTTPRequestDomainDetector',

        'domains' => [
            'testing'       => ['test.domain'], //Don't remove. Its used on unit testing
            'production'    => ['dev.buildr-framework.io'],
            'development'   => ['buildr.zolli.hu'],
        ],
    ],

    /**
     * Cache config
     */
    'cache' => [
        'driver' => 'buildr\Cache\Driver\RuntimeCacheDriver'
    ],

    /**
     * Service Providers.
     *
     * There are two types of serviceProviders. The forced providers loaded
     * during the boot phase and always available.
     *
     * The services that provider by optional providers should be always available
     * but not loaded during the boot, only if try to use the corresponding Facade
     * or try to take the named service out from the container.
     */
    'serviceProviders' => [
        'forced' => [
            //dummyServiceProvider::class
        ],

        'optional' => [
            'cache'         => CacheServiceProvider::class,
            'filesystem'    => FilesystemServiceProvider::class,
            'config'        => ConfigServiceProvider::class,
            'logger'        => LoggerServiceProvider::class,
        ],
    ],

];
