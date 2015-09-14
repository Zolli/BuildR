<?php namespace buildr\Application;

use buildr\Container\ContainerInterface;
use buildr\Http\Uri\Uri;
use buildr\Loader\PSR4ClassLoader;
use buildr\Router\Exception\RouteNotFoundException;
use buildr\Router\Route\Route;
use buildr\Router\Router;
use buildr\Startup\BuildrStartup;

/**
 * Main application constructor
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Application
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class Application {

    const FAIL_HANDLER_NAME = 'defaultFailHandler';

    /**
     * @type \buildr\Container\ContainerInterface
     */
    private static $container;

    /**
     * @type string
     */
    private $appNamespacePrefix;

    /**
     * @type \buildr\Http\Request\RequestInterface
     */
    private $request;

    /**
     * Constructor
     */
    public function __construct() {
        $this->request = self::getContainer()->get('request');
    }

    /**
     * Initialize
     */
    public function initialize($config) {
        //Get the autoloader and basePath
        $autoloader = BuildrStartup::getAutoloader();
        $basePath = BuildrStartup::getBasePath();

        //Get the class loader and register tha application namespace
        /** @var \buildr\Loader\PSR4ClassLoader $loader */
        $loader = $autoloader->getLoaderByName(PSR4ClassLoader::NAME)[0];

        $appAbsolute = realpath($basePath . $config['location']) . DIRECTORY_SEPARATOR;
        $loader->registerNamespace($config['namespaceName'], $appAbsolute);

        //Create a constant with application namespace prefix
        define('APP_NS', $config['namespaceName']);
    }

    /**
     * Run the application from the defined namespace root.
     *
     * @param string $namespace
     *
     * @return \buildr\Http\Response\ResponseInterface|NULL
     *
     * @codeCoverageIgnore
     */
    public function run($namespace) {
        $this->appNamespacePrefix = $namespace;

        //Initialize the routing. In this phase run the loading of all defined routes
        /** @type \buildr\Router\Router $router */
        $router = $this->registerRoutes();
        return $this->runRouteMatcher($router);
    }

    /**
     * Matches the request to registered routes
     *
     * @param \buildr\Router\Router $router
     *
     * @return \buildr\Router\Route\Route|FALSE
     *
     * @codeCoverageIgnore
     */
    private function runRouteMatcher(Router $router) {
        $matcher = $router->getMatcher();
        $route = $matcher->match($this->request);

        if(!$route) {
            $route = $this->getFailedHandler($router);
        }

        //In this phase we always have a proper Route object so try to call registered middlewares
        if(!empty(($middlewares = $route->middlewares))) {
            foreach($middlewares as $middleware) {
                $this->callRouteMiddleware($middleware, $route, $this->request);
            }
        }

        return $this->callRouteHandler($route);
    }

    /**
     * Run the given route middleware
     *
     * @param callable|string $middleware
     * @param \buildr\Router\Route\Route $route
     * @param \buildr\Http\Request\RequestInterface $request
     *
     * @codeCoverageIgnore
     */
    private function callRouteMiddleware($middleware, $route, $request) {
        if(is_string($middleware)) {
            list($class, $method) = explode('::', $middleware);

            $controller = self::getContainer()->construct($class);
            $middleware = [$controller, $method];
        }

        call_user_func_array($middleware, [$request, $route]);
    }

    /**
     * Run the router initialization, and execute
     * the routing registration.
     *
     * @return \buildr\Router\RouterInterface
     *
     * @codeCoverageIgnore
     */
    private function registerRoutes() {
        $applicationRouterClass = $this->appNamespacePrefix . 'Core\Http\Routing';

        /** @type \buildr\Contract\Application\ApplicationRoutingContract $routeRegistry */
        $routeRegistry = new $applicationRouterClass;

        /** @type \buildr\Router\Router $router; */
        $router = self::getContainer()->get('router');
        $router->setBasePath($this->getBasePath());

        $routeRegistry->register($router);

        return $router;
    }

    /**
     * Call the given route handler function
     *
     * @param \buildr\Router\Route\Route $route
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    private function callRouteHandler(Route $route) {
        $handler = $route->handler;

        if(is_string($handler)) {
            list($class, $method) = explode('::', $handler);

            $controller = self::getContainer()->construct($class);
            $handler = [$controller, $method];
        }

        return call_user_func_array($handler, [$route]);
    }

    /**
     * Returns a route that can handle the failed routing
     *
     * @param \buildr\Router\Router $router
     *
     * @return \buildr\Router\Route\Route
     *
     * @codeCoverageIgnore
     */
    private function getFailedHandler(Router $router) {
        if($router->hasFailedHandler()) {
            try {
                return $router->getFailedHandlerRoute();
            } catch(RouteNotFoundException $e) {
                return $this->registerFallbackFailHandler($router);
            }
        }

        return $this->registerFallbackFailHandler($router);
    }

    /**
     * Register a simple route that matches any request and return it as error handler
     *
     * @param \buildr\Router\Router $router
     *
     * @return \buildr\Router\Route\Route
     */
    private function registerFallbackFailHandler(Router $router) {
        $map = $router->getMap();

        $route = $map->any(self::FAIL_HANDLER_NAME, '/' . self::FAIL_HANDLER_NAME, function($route) use ($router) {
            //@codeCoverageIgnoreStart
            $failed = $router->getMatcher()->getFailedRoute();

            echo $failed->failedRule;
            exit;
            //@codeCoverageIgnoreEnd
        });

        return $route;
    }

    /**
     * Returns the current runtime base path.
     *
     * @return string
     */
    public function getBasePath($scriptName = NULL) {
        //@codeCoverageIgnoreStart
        if($scriptName === NULL) {
            /** @type \buildr\Http\Request\Request $request */
            $request = self::$container->get('request');

            $scriptName = $request->getGlobal('SCRIPT_NAME', '/index.php');
        }
        //@codeCoverageIgnoreEnd

        $basePathParts = array_filter(explode(Uri::PATH_SEPARATOR, $scriptName));

        //If the array is contains only one element that means the request run from the base path
        if(count($basePathParts) <= 1) {
            return '';
        }

        array_pop($basePathParts);
        return Uri::PATH_SEPARATOR . implode(Uri::PATH_SEPARATOR, $basePathParts);
    }

    /**
     * Set the container instance on the application
     *
     * @param \buildr\Container\ContainerInterface $c
     */
    public static function setContainer(ContainerInterface $c) {
        self::$container = $c;
    }

    /**
     * Get the DI container for application
     *
     * @return \buildr\Container\ContainerInterface
     */
    public static function getContainer() {
        return self::$container;
    }

}
