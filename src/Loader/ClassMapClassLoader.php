<?php namespace buildr\Loader;

use buildr\Loader\ClassLoaderInterface;

/**
 * ClassMap class loader implementation
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Loader
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class ClassMapClassLoader implements ClassLoaderInterface {

    const NAME = "classMapClassLoader";

    /**
     * @type int
     */
    private $priority = 10;

    /**
     * @type array
     */
    private $registeredFiles = [];

    /**
     * @type array
     */
    private $registeredClassMap = [];

    /**
     * Called on loader registration. Its allow to listen to registration event
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function register() {
        $this->preLoadRegisteredFiles();
    }

    /**
     * Load the specified class
     *
     * @param string $className
     *
     * @return bool
     */
    public function load($className) {
        if(!isset($this->registeredClassMap[$className])) {
            return FALSE;
        }

        $file = $this->registeredClassMap[$className];

        if(file_exists($file)) {
            include_once $file;

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Register a file for pre loading
     *
     * @param string $file
     */
    public function registerFile($file) {
        $this->registeredFiles[] = $file;
    }

    /**
     * Register a classMap for autoloading
     *
     * @param array $classMap
     */
    public function registerClassMap($classMap) {
        if(!is_array($classMap)) {
            throw new \InvalidArgumentException("The class map must be an array!");
        }

        $this->registeredClassMap = array_merge($this->registeredClassMap, $classMap);
    }

    /**
     * Called on register() function, in laoder registration, preload all registered files
     */
    private function preLoadRegisteredFiles() {
        foreach ($this->registeredFiles as $preloadFile) {
            if(file_exists($preloadFile)) {
                include_once $preloadFile;
            }
        }
    }

    /**
     * The loader unique name
     *
     * @return string
     */
    public function getName() {
        return self::NAME;
    }

    /**
     * Return the priority of this loader. It's not a constant!
     *
     * @return int
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * Set the priority of this autolaoder. Its called on registration when the pre-specified priority
     * is already reserved by another loader
     *
     * @param int $priority
     *
     * @return void
     */
    public function setPriority($priority) {
        if(!is_int($priority)) {
            throw new \InvalidArgumentException();
        }

        $this->priority = $priority;
    }

}
