<?php namespace buildr\Router\Map;

use buildr\Router\Exception\RouteAlreadyExistException;
use buildr\Router\Exception\RouteNotFoundException;
use buildr\Router\Route\Route;
use \IteratorAggregate;
use \ArrayIterator;
use \Traversable;

/**
 * Route map
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Router\Map
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 *
 * @method \buildr\Router\Map\RouteMap accepts(array $accept) Set what the route accepts
 * @method \buildr\Router\Map\RouteMap allows(array $allows) Set what HTTP method that route allows
 * @method \buildr\Router\Map\RouteMap attributes(array $attributes) Add new attributes to the route
 * @method \buildr\Router\Map\RouteMap defaults(array $defaults) Set the default optional attribute value for route
 * @method \buildr\Router\Map\RouteMap extras(array $extras) Add new extra values to the route
 * @method \buildr\Router\Map\RouteMap host(string $host) Set what host is route matching
 * @method \buildr\Router\Map\RouteMap secure(bool $value = TRUE) This route allow insecure access or only secure
 * @method \buildr\Router\Map\RouteMap tokens(array $tokens) Add new tokens to this route
 * @method \buildr\Router\Map\RouteMap wildcard(string $wildcard) Set route to wildcard, and set the wildcard key name
 * @method \buildr\Router\Map\RouteMap middleware(array $middleware, bool $override) Add a new middleware to the route
 */
class RouteMap implements IteratorAggregate {

    /**
     * @type \buildr\Router\Route\Route
     */
    private $routePrototype;

    /**
     * @type \buildr\Router\Route\Route[]
     */
    private $routes = [];

    /**
     * Constructor
     *
     * @param \buildr\Router\Route\Route $routePrototype
     */
    public function __construct(Route $routePrototype) {
        $this->routePrototype = $routePrototype;
    }

    /**
     * Proxy unknown method calls to the proto-route
     *
     * @param string $method
     * @param string $params
     *
     * @return $this
     */
    public function __call($method, $params) {
        call_user_func_array([$this->routePrototype, $method], $params);

        return $this;
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable An instance of an object implementing Traversable
     */
    public function getIterator() {
        return new ArrayIterator($this->routes);
    }

    /**
     * Set routes by array
     *
     * @param array $routes
     */
    public function setRoutes(array $routes) {
        $this->routes = $routes;
    }

    /**
     * Get defined routes as array
     *
     * @return \buildr\Router\Route\Route[]
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Add a new route definition
     *
     * @param \buildr\Router\Route\Route $route
     *
     * @throws \buildr\Router\Exception\RouteAlreadyExistException
     */
    public function addRoute(Route $route) {
        $name = $route->name;

        //Unnamed route
        if(!$name) {
            $this->routes[] = $route;

            return;
        }

        //Check for already registered route with this name
        if(isset($this->routes[$name])) {
            $message = 'The route (' . $name . ') is already exist';
            throw new RouteAlreadyExistException($message);
        }

        $this->routes[$name] = $route;
    }

    /**
     * Returns a named route
     *
     * @param string $name
     *
     * @return \buildr\Router\Route\Route
     * @throws \buildr\Router\Exception\RouteNotFoundException
     */
    public function getRoute($name) {
        if(!isset($this->routes[$name])) {
            $message = 'The route (' . $name . ') is not found';
            throw new RouteNotFoundException($message);
        }

        return $this->routes[$name];
    }

    /**
     * Add a new generic route
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *
     * @return \buildr\Router\Route\Route
     * @throws \buildr\Router\Exception\ImmutablePropertyException
     * @throws \buildr\Router\Exception\RouteAlreadyExistException
     */
    public function route($name, $path, $handler = NULL) {
        $route = clone $this->routePrototype;

        $route->name($name);
        $route->path($path);
        $route->handler($handler);

        $this->addRoute($route);

        return $route;
    }

    /**
     * Add a new route tha matches ANY HTTP method
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *
     * @return \buildr\Router\Route\Route
     *
     * @codeCoverageIgnore
     */
    public function any($name, $path, $handler = NULL) {
        $route = $this->route($name, $path, $handler);
        $route->allows(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS']);

        return $route;
    }

    /**
     * Add a new GET route
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *
     * @return \buildr\Router\Route\Route
     *
     * @codeCoverageIgnore
     */
    public function get($name, $path, $handler = NULL) {
        $route = $this->route($name, $path, $handler);
        $route->allows('GET');

        return $route;
    }

    /**
     * Add a new DELETE route
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *
     * @return \buildr\Router\Route\Route
     *
     * @codeCoverageIgnore
     */
    public function delete($name, $path, $handler = NULL) {
        $route = $this->route($name, $path, $handler);
        $route->allows('DELETE');

        return $route;
    }

    /**
     * Add a new HEAD route
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *
     * @return \buildr\Router\Route\Route
     *
     * @codeCoverageIgnore
     */
    public function head($name, $path, $handler = NULL) {
        $route = $this->route($name, $path, $handler);
        $route->allows('HEAD');

        return $route;
    }

    /**
     * Add a new OPTIONS route
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *
     * @return \buildr\Router\Route\Route
     *
     * @codeCoverageIgnore
     */
    public function options($name, $path, $handler = NULL) {
        $route = $this->route($name, $path, $handler);
        $route->allows('OPTIONS');

        return $route;
    }

    /**
     * Add a new PATCH route
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *
     * @return \buildr\Router\Route\Route
     *
     * @codeCoverageIgnore
     */
    public function patch($name, $path, $handler = NULL) {
        $route = $this->route($name, $path, $handler);
        $route->allows('PATCH');

        return $route;
    }

    /**
     * Add a new POST route
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *
     * @return \buildr\Router\Route\Route
     *
     * @codeCoverageIgnore
     */
    public function post($name, $path, $handler = NULL) {
        $route = $this->route($name, $path, $handler);
        $route->allows('POST');

        return $route;
    }

    /**
     * Add a new PUT route
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *
     * @return \buildr\Router\Route\Route
     *
     * @codeCoverageIgnore
     */
    public function put($name, $path, $handler = NULL) {
        $route = $this->route($name, $path, $handler);
        $route->allows('PUT');

        return $route;
    }

    /**
     * Attach a map to an existing map
     *
     * @param string $namePrefix
     * @param string $pathPrefix
     * @param callable $callable
     *
     * @throws \buildr\Router\Exception\ImmutablePropertyException
     */
    public function attach($namePrefix, $pathPrefix, callable $callable) {
        $old = $this->routePrototype;

        $new = clone $old;

        $new->namePrefix($old->namePrefix . $namePrefix . '.');
        $new->pathPrefix($old->pathPrefix . $pathPrefix);
        $new->middleware([], TRUE);

        $this->routePrototype = $new;

        $callable($this);
        $this->routePrototype = $old;
    }

}
