<?php namespace buildr\Router;

use buildr\Application\Application;
use buildr\Router\Generator\UrlGenerator;
use buildr\Router\Map\RouteMap;
use buildr\Router\Matcher\RouteMatcher;
use buildr\Router\Route\Route;
use buildr\Router\Rule\AllowRule;
use buildr\Router\Rule\PathRule;
use buildr\Router\Rule\RuleIterator;

/**
 * Main request router
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Router
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class Router {

    /**
     * Router base path
     *
     * @type NULL|string
     */
    private $basePath;

    /**
     * @type callable
     */
    private $routeFactory;

    /**
     * @type callable
     */
    private $mapFactory;

    /**
     * @type callable
     */
    private $mapBuilder;

    /**
     * @type \buildr\Router\Rule\RuleIterator
     */
    private $ruleIterator;

    /**
     * @type \buildr\Router\Route\Route
     */
    private $route;

    /**
     * @type \buildr\Router\Map\RouteMap
     */
    private $map;

    /**
     * @type \buildr\Router\Matcher\RouteMatcher
     */
    private $matcher;

    /**
     * @type \buildr\Router\Generator\UrlGenerator
     */
    private $generator;

    /**
     * @type string
     */
    private $failedHandler;

    /**
     * Constructor
     */
    public function __construct() {
        $self = $this;

        $this->setRouteFactory(function() {
            return new Route();
        });

        $this->setMapFactory(function() use ($self) {
            return new RouteMap($self->getRoute());
        });

        $this->setMapBuilder(function(RouteMap $map) {

        });
    }

    /**
     * Set tha route name that called when the route handling is not matched
     *
     * @param string $name
     *
     * @codeCoverageIgnore
     */
    public function setFailedHandlerName($name) {
        $this->failedHandler = $name;
    }

    /**
     * Returns the route that set as handler when the matching is failed
     *
     * @return \buildr\Router\Route\Route
     * @throws \buildr\Router\Exception\RouteNotFoundException
     */
    public function getFailedHandlerRoute() {
        return $this->getMap()->getRoute($this->failedHandler);
    }

    /**
     * Determines that the router has a failed route handler or not.
     *
     * @return bool
     */
    public function hasFailedHandler() {
        return isset($this->failedHandler);
    }

    /**
     * Set the router base path
     *
     * @param string|NULL $basePath
     *
     * @codeCoverageIgnore
     */
    public function setBasePath($basePath) {
        if(!empty($basePath)) {
            $this->basePath = $basePath;
        }
    }

    /**
     * Set the router route factory method
     *
     * @param callable $factory
     */
    public function setRouteFactory(callable $factory) {
        $this->routeFactory = $factory;
    }

    /**
     * Set the router map factory method
     *
     * @param callable $factory
     */
    public function setMapFactory(callable $factory) {
        $this->mapFactory = $factory;
    }

    /**
     * Sets the map builder
     *
     * @param callable $builder
     */
    public function setMapBuilder(callable $builder) {
        $this->mapBuilder = $builder;
    }

    /**
     * Returns the route map
     *
     * @return \buildr\Router\Map\RouteMap
     */
    public function getMap() {
        if(!$this->map) {
            $this->map = call_user_func($this->mapFactory);
            call_user_func($this->mapBuilder, $this->map);
        }

        return $this->map;
    }

    /**
     * Get a shared route instance
     *
     * @return \buildr\Router\Route\Route
     */
    public function getRoute() {
        if(!$this->route) {
            $this->route = call_user_func($this->routeFactory);
        }

        return $this->route;
    }

    /**
     * Returns a shared matcher
     *
     * @return \buildr\Router\Matcher\RouteMatcher
     */
    public function getMatcher() {
        if(!$this->matcher) {
            $this->matcher = new RouteMatcher(
                $this->getMap(),
                $this->getRuleIterator()
            );
        }

        return $this->matcher;
    }

    /**
     * Returns a shared generator
     *
     * @return \buildr\Router\Generator\UrlGenerator
     */
    public function getGenerator() {
        $request = Application::getContainer()->get('request');

        if(!$this->generator) {
            $this->generator = new UrlGenerator($request, $this->getMap(), $this->basePath);
        }

        return $this->generator;
    }

    /**
     * Return a shared ruleIterator instance
     *
     * @return \buildr\Router\Rule\RuleIterator
     */
    public function getRuleIterator() {
        if(!$this->ruleIterator) {
            $this->ruleIterator = new RuleIterator([
                new PathRule(),
                new AllowRule(),
            ]);
        }

        return $this->ruleIterator;
    }

}
