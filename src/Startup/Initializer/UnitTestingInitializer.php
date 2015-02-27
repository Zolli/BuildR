<?php namespace buildr\Startup\Initializer;

use buildr\Loader\classLoader;
use buildr\Startup\BuildrEnvironment;

/**
 * BuildR - PHP based continuous integration server
 *
 * Unit testing bootup initializer
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Startup\Initializer
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class UnitTestingInitializer extends BaseInitializer implements InitializerInterface {

    /**
     * Run the startup initialization process
     *
     * @param string $basePath
     * @param \buildr\Loader\classLoader $autoloader
     * @return bool
     */
    public function initialize($basePath, classLoader $autoloader) {
        BuildrEnvironment::isRunningUnitTests();

        $PSR4Loader = $autoloader->getLoaderByName(\buildr\Loader\PSR4ClassLoader::NAME)[0];
        $testsPath = realpath($basePath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'buildr') . DIRECTORY_SEPARATOR;
        $PSR4Loader->registerNamespace('buildr\\tests\\', $testsPath);

        $this->registerServiceProviders();
    }
}