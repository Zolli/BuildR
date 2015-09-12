<?php namespace buildr\Startup;

use buildr\Loader\ClassLoader;
use buildr\Loader\classMapClassLoader;
use buildr\Loader\PSR4ClassLoader;
use buildr\Startup\Exception\StartupException;
use Closure;

/**
 * Startup class
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Registry
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class BuildrStartup {

    /**
     * @type flaot
     */
    private static $startupTime;

    /**
     * @type ClassLoader
     */
    private static $loader;

    /**
     * @type string
     */
    private static $basePath;

    /**
     * Set the initializer class to be executed
     *
     * @param \Closure|\buildr\Startup\Initializer\InitializerInterface $initializer
     *
     * @throws \buildr\Startup\Exception\StartupException
     *
     * @codeCoverageIgnore
     */
    public function setInitializer($initializer) {
        self::$startupTime = microtime(TRUE);

        if(self::$basePath == NULL) {
            throw new StartupException("You must set up the basePath before initializing!");
        }

        $basePath = self::getBasePath();
        $autoloader = self::getAutoloader();

        if($initializer instanceof Closure) {
            call_user_func_array($initializer, [
                $basePath,
                $autoloader
            ]);

            return;
        }

        $initializerClass = new $initializer();
        $initializerClass->initialize($basePath, $autoloader);
    }

    /**
     * Set the absolute base path of the current project
     *
     * @param string $basePath
     */
    public static final function setBasePath($basePath) {
        self::$basePath = $basePath;
    }

    /**
     * Return the absolute base path of the current project
     *
     * @return string
     */
    public static final function getBasePath() {
        return self::$basePath;
    }

    /**
     * @param bool $withComposer
     *
     * @throws \buildr\Startup\Exception\StartupException
     *
     * @codeCoverageIgnore
     */
    public static final function initializeAutoloading($withComposer = TRUE) {
        if(self::$basePath == NULL) {
            throw new StartupException("You must set up the basePath before initializing the autoloader!");
        }

        $basePath = self::getBasePath();

        //If we need composer autoloader, try to include it
        if($withComposer === TRUE) {
            //Loading composer's autolaoder, we must to use it, because some package not provide proper autolaoder
            $composerLoaderLocation = [
                $basePath,
                'vendor',
                'autoload.php'
            ];
            $composerLoaderLocation = implode(DIRECTORY_SEPARATOR, $composerLoaderLocation);
            $composerLoaderLocation = realpath($composerLoaderLocation);

            if(file_exists($composerLoaderLocation)) {
                //Load classLoader
                require_once $composerLoaderLocation;
            }
        }

        //System-safe absolute path generation
        $classLoaderLocation = [
            $basePath,
            'src',
            'Loader',
            'ClassLoader.php'
        ];
        $classLoaderLocation = implode(DIRECTORY_SEPARATOR, $classLoaderLocation);
        $classLoaderLocation = realpath($classLoaderLocation);

        //Load classLoader
        require_once $classLoaderLocation;

        //Initialize and set-up autoloading
        ClassLoader::loadAutoLoader();
        self::$loader = new ClassLoader();

        //Initialize PSr-4 auto-loading
        $PSR4Loader = new PSR4ClassLoader();
        $sourceAbsolute = realpath($basePath . DIRECTORY_SEPARATOR . 'src') . DIRECTORY_SEPARATOR;
        $PSR4Loader->registerNamespace('buildr\\', $sourceAbsolute);

        //Initialize ClassMap based class loading
        $classMapLoader = new classMapClassLoader();
        $classMapLoader->registerFile(realpath($basePath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Utils' .
                                                DIRECTORY_SEPARATOR . 'Debug' . DIRECTORY_SEPARATOR .
                                                'DebugHelper.php'));

        $classMapLocation = $basePath . DIRECTORY_SEPARATOR . 'classMap.php';

        if(file_exists($classMapLocation)) {
            $classMap = require $basePath . DIRECTORY_SEPARATOR . 'classMap.php';
            $classMapLoader->registerClassMap($classMap);
        }

        self::$loader->registerLoader($PSR4Loader);
        self::$loader->registerLoader($classMapLoader);
        self::$loader->initialize();
    }

    /**
     * Returns the main instance of the autoloader
     *
     * @return ClassLoader
     */
    public static final function getAutoloader() {
        return self::$loader;
    }

    /**
     * Return the startup time in microseconds
     *
     * @return float
     */
    public static function getStartupTime() {
        return self::$startupTime;
    }

    /**
     * Get time since startup in microseconds
     *
     * @return float
     */
    public static final function getTimeSinceStartup() {
        $currentTime = microtime(TRUE);

        return $currentTime - self::getStartupTime();
    }

}
