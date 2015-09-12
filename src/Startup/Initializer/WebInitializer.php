<?php namespace buildr\Startup\Initializer;

use buildr\Application\Application;
use buildr\Loader\classLoader;
use buildr\Startup\BuildrEnvironment;
use buildr\Http\Request\RequestServiceProvider;
use buildr\Http\Response\ResponseServiceProvider;
use buildr\Router\RouterServiceProvider;
use buildr\Application\ApplicationServiceProvider;
use Patchwork\Utf8\Bootup;

/**
 * Initializer class for web requests
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Startup\Initializer
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 *
 * @codeCoverageIgnore
 */
class WebInitializer extends BaseInitializer {

    /**
     * Run the startup initialization process
     *
     * @param string $basePath
     * @param \buildr\Loader\classLoader $autoloader
     *
     * @return bool
     */
    public function initialize($basePath, classLoader $autoloader) {
        //Initialize Patchwork-UTF8 mbstring library if a common mbstring function does not exist
        if(!function_exists('mb_substr')) {
            Bootup::initMbstring();
        }

        //Environment detection
        BuildrEnvironment::detectEnvironment();

        //Register additional providers, that exist only in web requests
        $this
            ->addProvider(RequestServiceProvider::class)
            ->addProvider(ResponseServiceProvider::class)
            ->addProvider(RouterServiceProvider::class)
            ->addProvider(ApplicationServiceProvider::class);

        parent::initialize($basePath, $autoloader);
    }
}
