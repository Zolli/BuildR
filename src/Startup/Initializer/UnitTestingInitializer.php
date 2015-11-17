<?php namespace buildr\Startup\Initializer;

use buildr\Loader\classLoader;
use buildr\Loader\PSR4ClassLoader;
use buildr\Startup\BuildrEnvironment;

/**
 * Unit testing bootup initializer
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
class UnitTestingInitializer extends BaseInitializer {

    /**
     * Run the startup initialization process
     *
     * @param string $basePath
     * @param \buildr\Loader\classLoader $autoloader
     *
     * @return bool
     */
    public function initialize($basePath, classLoader $autoloader) {
        $PSR4Loader = $autoloader->getLoaderByName(PSR4ClassLoader::NAME)[0];
        $testsPath = realpath($basePath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'buildr') .
                                DIRECTORY_SEPARATOR;
        $PSR4Loader->registerNamespace('buildr\\tests\\', $testsPath);

        parent::initialize($basePath, $autoloader);
    }
}
