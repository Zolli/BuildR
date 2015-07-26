<?php namespace buildr\Startup\Initializer;

use buildr\Startup\Initializer\InitializerInterface;
use buildr\Application\Application;
use buildr\Config\Config;
use buildr\Container\Container;
use buildr\Container\Repository\InMemoryServiceRepository;
use buildr\Loader\classLoader;
use buildr\ServiceProvider\ServiceProvider;

/**
 * Base initializer
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
class BaseInitializer implements InitializerInterface {

    private $additionalProviders = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->constructContainer();
    }

    /**
     * Add an additional provider that loaded during tha startup process
     *
     * @param string $providerClass
     *
     * @return \buildr\Startup\Initializer\InitializerInterface
     */
    public function addProvider($providerClass) {
        $this->additionalProviders[] = $providerClass;

        return $this;
    }

    /**
     * Run the startup initialization process
     *
     * @param string $basePath
     * @param \buildr\Loader\classLoader $autoloader
     *
     * @return bool
     */
    public function initialize($basePath, classLoader $autoloader) {
        $this->registerServiceProviders();
    }

    /**
     * @return \buildr\Container\Container
     */
    public function constructContainer() {
        $containerRepository = new InMemoryServiceRepository();
        $container = new Container($containerRepository);
        $container->add('buildr', $container);

        Application::setContainer($container);
    }

    /**
     * Register the service providers in the registry
     */
    protected function registerServiceProviders() {
        $serviceProviders = Config::getProviderConfig();
        $serviceProviders = array_merge($serviceProviders, $this->additionalProviders);

        ServiceProvider::registerProvidersByArray($serviceProviders);
    }
}
