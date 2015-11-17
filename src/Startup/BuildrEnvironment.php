<?php namespace buildr\Startup;

use buildr\Config\Config;
use buildr\Startup\Environment\Detector\EnvironmentException;
use buildr\Startup\Environment\EnvironmentDetector;

/**
 * Environment handler
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Startup
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class BuildrEnvironment {

    /**
     * Production environment constant
     */
    const E_PROD = "production";

    /**
     * Development environment constant
     */
    const E_DEV = "development";

    /**
     * Default environment on console run
     */
    const E_CONSOLE = "console-default";

    /**
     * Environment for unit tests
     */
    const E_TESTING = "testing";

    /**
     * @type string
     */
    private static $currentEnvironment;

    /**
     * @type bool
     */
    private static $isInitialized = FALSE;

    /**
     * Returns the current environment of the application
     *
     * @return null|string
     */
    public static final function getEnv() {
        if(self::$isInitialized === FALSE) {
            return NULL;
        }

        return self::$currentEnvironment;
    }

    /**
     * Run the environment detection. If the callback is a Closuer will run teh closure
     * and returns the result, in other case get the detector class from the config
     * and use the detect() method as the closer
     *
     * @param \Closure|null $callback
     *
     * @throws \buildr\Startup\Environment\Detector\EnvironmentException
     * @codeCoverageIgnore
     */
    public static final function detectEnvironment($callback = NULL) {
        $consoleArgs = (isset($_SERVER['argv'])) ? $_SERVER['argv'] : NULL;
        $runningTest = defined('BUILDR_RUNNING_TESTS');

        if($runningTest === TRUE) {
            self::isRunningUnitTests();
            return;
        }

        if(!($callback instanceof \Closure)) {
            $envConfig = Config::getEnvDetectionConfig();
            $callback = self::getDetectorClosure($envConfig['detector']);
        }

        self::$currentEnvironment = (new EnvironmentDetector())->detect($callback, $consoleArgs);
        self::$isInitialized = TRUE;
    }

    /**
     * Function to manually override or set the environment
     *
     * @param string $environmentString
     *
     * @throws \buildr\Startup\Environment\Detector\EnvironmentException
     */
    public static function setEnv($environmentString) {
        if(!is_string($environmentString)) {
            throw new EnvironmentException("The setEnv() function must be take a string as argument!");
        }

        self::$isInitialized = TRUE;
        self::$currentEnvironment = $environmentString;
    }

    /**
     * Set environment to unit testing mode
     */
    public static final function isRunningUnitTests() {
        self::$isInitialized = TRUE;
        self::$currentEnvironment = self::E_TESTING;
    }

    /**
     * Returns the dynamically created closure from a detector class detect() method
     *
     * @param string $detectorClass
     *
     * @return \Closure
     * @throws \buildr\Startup\Environment\Detector\EnvironmentException
     */
    private static function getDetectorClosure($detectorClass) {
        try {
            $detectorReflector = new \ReflectionClass($detectorClass);
        } catch (\ReflectionException $e) {
            throw new EnvironmentException("The following detector class ({$detectorClass}) is not instantiable!");
        }

        if(!$detectorReflector->implementsInterface('buildr\Startup\Environment\Detector\DetectorInterface')) {
            throw new EnvironmentException("The class ({$detectorClass}) must be implements the DetectorInterface!");
        }

        $methodReflector = $detectorReflector->getMethod("detect");

        return $methodReflector->getClosure($detectorReflector->newInstance());
    }

}
