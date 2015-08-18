<?php namespace buildr\ServiceProvider;

use buildr\Application\Application;

/**
 * Service Provider. This class can handle all sort of service registration.
 * This class job is to register services and handle loading of optional services.
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage ServiceProvider
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class ServiceProvider {

    /**
     * @type array
     */
    private static $optionalServices = [];

    /**
     * @type array
     */
    private static $loadedOptionalServices = [];

    /**
     * Register forced service providers by an array.
     *
     * @param array $providersArray An array that contains service providers FQCN as key
     *
     * @throw \InvalidArgumentException
     */
    public static function registerProvidersByArray($providersArray) {
        if(!is_array($providersArray)) {
            throw new \InvalidArgumentException("This method must take an array as argument!");
        }

        foreach ($providersArray as $providerClassName) {
            self::registerByName($providerClassName);
        }
    }

    /**
     * Register optional service providers by array
     *
     * @param array $optionalProviders
     *
     * @throw \InvalidArgumentException
     */
    public static function addOptionalsByArray($optionalProviders) {
        if(!is_array($optionalProviders)) {
            throw new \InvalidArgumentException('This method must take an array as argument!');
        }

        foreach($optionalProviders as $bindingName => $providerClass) {
            self::addOptionalProvider($bindingName, $providerClass);
        }
    }

    /**
     * Register an optional service provider.
     *
     * @param string $serviceName The service short name (eg. binding name)
     * @param string $serviceClassName The service provider fully qualified class name
     *
     * @return bool
     */
    public static function addOptionalProvider($serviceName, $serviceClassName) {
        if(!isset(self::$optionalServices[$serviceName])) {
            self::$optionalServices[$serviceName] = $serviceClassName;
            self::registerOptionalProviderBindings($serviceClassName);

            return TRUE;
        }

        $msg = 'The service name (' . $serviceName . ') is already taken by ' . self::$optionalServices[$serviceName];
        throw new \LogicException($msg);
    }

    public static function registerOptionalProviderBindings($serviceClassName) {
        if(!class_exists($serviceClassName)) {
            return;
        }

        $serviceClass = new $serviceClassName;
        $container = Application::getContainer();

        if(($providedInterfaces = $serviceClass->provides()) !== NULL) {
            foreach($providedInterfaces as $interfaceClass) {
                $container->bind($interfaceClass, $serviceClass->getBindingName(), TRUE);
            }
        }
    }

    /**
     * Checks that the given service name is a service name, that an
     * optional provider can provide.
     *
     * @param string $serviceName The service short name (eg. binding name)
     *
     * @return bool
     */
    public static function isOptionalService($serviceName) {
        return isset(self::$optionalServices[$serviceName]);
    }

    /**
     * Determines that the given optional provider is already
     * loaded or not.
     *
     * @param string $serviceName The service short name (eg. binding name)
     *
     * @return bool
     */
    public static function isOptionalServiceLoaded($serviceName) {
        return isset(self::$loadedOptionalServices[$serviceName]);
    }

    /**
     * Initialize the loading of an optional service.
     * It uses the same registration process that the forced providers use.
     *
     * @param string $serviceName The service short name (eg. binding name)
     */
    public static function loadOptionalService($serviceName) {
        $providerClassName = self::$optionalServices[$serviceName];
        self::registerByName($providerClassName);

        self::$loadedOptionalServices[$serviceName] = TRUE;
    }

    /**
     * Invoke the registration process of the given service provider class.
     *
     * @param string $providerName The provider fully qualified class name
     */
    public static function registerByName($providerName) {
        $container = Application::getContainer();
        $providerClass = self::checkProviderByName($providerName);

        $ObjectToRegister = $providerClass->register();
        $bindingName = $providerClass->getBindingName();

        $container->add($bindingName, $ObjectToRegister);

        if(($providedInterfaces = $providerClass->provides()) !== NULL) {
            foreach($providedInterfaces as $interfaceClass) {
                $container->bind($interfaceClass, $bindingName, TRUE);
            }
        }
    }

    /**
     * Check provider by name and return the instated class
     *
     * @param string $providerName The provider fully qualified class name
     *
     * @return \buildr\ServiceProvider\ServiceProviderInterface
     */
    private static function checkProviderByName($providerName) {
        if(!class_exists($providerName)) {
            throw new \RuntimeException("The provider class ({$providerName}) not found!");
        }

        $providerClass = new $providerName;

        if(!($providerClass instanceof ServiceProviderInterface)) {
            throw new \RuntimeException("Provider ({$providerName}) must be implement ServiceProviderInterface!");
        }

        return $providerClass;
    }

}
