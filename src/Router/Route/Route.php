<?php namespace buildr\Router\Route;

use buildr\Router\Exception\ImmutablePropertyException;

/**
 * Route definition
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Router\Route
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 *
 * @property-read string $name The route name.
 * @property-read string $path The route path.
 * @property-read string $namePrefix
 * @property-read string $pathPrefix
 * @property-read string $host
 * @property-read mixed $handler
 * @property-read array $defaults Default values for attributes.
 * @property-read array $attributes Attribute values added by the rules.
 * @property-read array $tokens Placeholder token name and regex.
 * @property-read string $wildcard The name of the wildcard token.
 * @property-read array $accepts
 * @property-read array $extras
 * @property-read bool $secure
 * @property-read array $allows
 * @property-read bool $isRouteable
 * @property-read string $failedRule
 * @property-read array $middlewares
 *
 */
class Route {

    /**
     * @type array
     */
    protected $attributes = [];

    /**
     * @type array
     */
    protected $defaults = [];

    /**
     * @type array
     */
    protected $accepts = [];

    /**
     * @type array
     */
    protected $allows = [];

    /**
     * @type array
     */
    protected $extras = [];

    /**
     * @type array
     */
    protected $tokens = [];

    /**
     * @type array
     */
    protected $middlewares = [];

    /**
     * @type string
     */
    protected $name;

    /**
     * @type string|NULL
     */
    protected $namePrefix;

    /**
     * @type string
     */
    protected $path;

    /**
     * @type string|NULL
     */
    protected $pathPrefix;

    /**
     * @type mixed
     */
    protected $handler;

    /**
     * @type string|NULL
     */
    protected $host;

    /**
     * @type bool
     */
    protected $isRouteable = TRUE;

    /**
     * @type bool
     */
    protected $secure = FALSE;

    /**
     * @type string|NULL
     */
    protected $wildcard;

    /**
     * @type string
     */
    protected $failedRule;

    /**
     * When clone the route reset the attributes and failedRule attribute
     */
    public function __clone() {
        $this->attributes = $this->defaults;
        $this->failedRule = NULL;
    }

    /**
     * Getter for read-only property
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function __get($key) {
        return $this->$key;
    }

    /**
     * Set what the route accepts
     *
     * @param array|string $accept
     *
     * @return $this
     */
    public function accepts($accept) {
        $this->accepts = array_merge($this->accepts, (array) $accept);

        return $this;
    }

    /**
     * Set what HTTP method that route allows
     *
     * @param array|string $allows
     *
     * @return $this
     */
    public function allows($allows) {
        $this->allows = array_merge($this->allows, (array) $allows);

        return $this;
    }

    /**
     * Add new attributes to the route
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function attributes(array $attributes) {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * Set the default optional attribute value for route
     *
     * @param array $defaults
     *
     * @return $this
     */
    public function defaults(array $defaults) {
        $this->defaults = array_merge($this->defaults, $defaults);

        return $this;
    }

    /**
     * Add new extra values to the route
     *
     * @param array $extras
     *
     * @return $this
     */
    public function extras(array $extras) {
        $this->extras = array_merge_recursive($this->extras, $extras);

        return $this;
    }

    /**
     * Set the failed rule when the route failed to match
     *
     * @param string $failedRule
     *
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function failedRule($failedRule) {
        $this->failedRule = $failedRule;

        return $this;
    }

    /**
     * Set the route handler
     *
     * @param mixed $handler
     *
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function handler($handler) {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Set what host is route matching
     *
     * @param string $host
     *
     * @return $this
     */
    public function host($host) {
        $this->host = $host;

        return $this;
    }

    /**
     * Set the route routeable or not
     *
     * @param bool $value
     *
     * @return $this
     */
    public function isRouteable($value = TRUE) {
        $this->isRouteable = (bool) $value;

        return $this;
    }

    /**
     * Set the route name
     *
     * @param string $name
     *
     * @return $this
     * @throws \buildr\Router\Exception\ImmutablePropertyException
     */
    public function name($name) {
        if($this->name !== NULL) {
            $message = __CLASS__ . '::$name is immutable';
            throw new ImmutablePropertyException($message);
        }

        $this->name = $this->namePrefix . $name;

        return $this;
    }

    /**
     * Set the route name prefix
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function namePrefix($prefix) {
        $this->namePrefix = $prefix;

        return $this;
    }

    /**
     * Set the route path
     *
     * @param string $path
     *
     * @return $this
     * @throws \buildr\Router\Exception\ImmutablePropertyException
     */
    public function path($path) {
        if($this->path !== NULL) {
            $message = __CLASS__ . '::$path is immutable';
            throw new ImmutablePropertyException($message);
        }

        $this->path = $this->pathPrefix . $path;

        return $this;
    }

    /**
     * Set the route path prefix
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function pathPrefix($prefix) {
        $this->pathPrefix = $prefix;

        return $this;
    }

    /**
     * This route allow insecure access or only secure
     *
     * @param bool|TRUE $value
     *
     * @return $this
     */
    public function secure($value = TRUE) {
        $this->secure = ($value === NULL) ? NULL : (bool) $value;

        return $this;
    }

    /**
     * Add new tokens to this route
     *
     * @param array $tokens
     *
     * @return $this
     */
    public function tokens(array $tokens) {
        $this->tokens = array_merge($this->tokens, $tokens);

        return $this;
    }

    /**
     * Set route to wildcard, and set the wildcard key name
     *
     * @param string $wildcard
     *
     * @return $this
     */
    public function wildcard($wildcard) {
        $this->wildcard = $wildcard;

        return $this;
    }

    /**
     * Add a new middleware to the route
     *
     * @param string|array $middleware
     * @param bool $override Ovverride the existing middlewares of this route
     *
     * @return $this;
     */
    public function middleware($middleware, $override = FALSE) {
        if($override) {
            $this->middlewares = $middleware;

            return $this;
        }

        $this->middlewares = array_merge($this->middlewares, (array) $middleware);

        return $this;
    }

}
