<?php namespace buildr\Router\Generator;

use buildr\Http\Request\RequestInterface;
use buildr\Router\Map\RouteMap;

/**
 * Route generator
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Router\Generator
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class UrlGenerator {

    /**
     * @type \buildr\Http\Request\RequestInterface
     */
    protected $request;

    /**
     * @type \buildr\Router\Map\RouteMap
     */
    protected $map;

    /**
     * @type \buildr\Router\Route\Route
     */
    protected $route;

    /**
     * @type string
     */
    protected $url;

    /**
     * @type array
     */
    protected $data;

    /**
     * @type array
     */
    protected $repl;

    /**
     * @type bool
     */
    protected $raw;

    /**
     * @type NULL|string
     */
    protected $basepath;

    /**
     * Constructor
     *
     * @param \buildr\Http\Request\RequestInterface $request
     * @param \buildr\Router\Map\RouteMap $map
     * @param NULL|string $basepath
     */
    public function __construct(RequestInterface $request, RouteMap $map, $basepath = NULL) {
        $this->request = $request;
        $this->map = $map;
        $this->basepath = $basepath;
    }

    /**
     * Generate a route URL by get the route by name and interpolate with
     * the given data.
     *
     * @param string $name The route name
     * @param array $data Interpolation data
     *
     * @return string
     */
    public function generate($name, $data = []) {
        return $this->build($name, $data, FALSE);
    }

    /**
     * Generate a route with the same method as generate() bu not encode
     * the values with rawurlencode()
     *
     * @param string $name The route name
     * @param array $data Interpolation data
     *
     * @return string
     */
    public function generateRaw($name, $data = []) {
        return $this->build($name, $data, TRUE);
    }

    /**
     * Generates the URL form the route
     *
     * @param string $name The route name
     * @param array $data Interpolation data
     * @param bool $raw If true disable values encoding
     *
     * @return string
     * @throws \buildr\Router\Exception\RouteNotFoundException
     */
    protected function build($name, $data, $raw) {
        $this->raw = $raw;
        $this->route = $this->map->getRoute($name);

        $this->buildUrl();

        $this->repl = [];
        $this->data = array_merge($this->route->defaults, $data);

        $this->buildTokenReplacements();
        $this->buildOptionalReplacements();

        $this->url = strtr($this->url, $this->repl);

        $this->buildWildcardReplacement();

        return $this->url;
    }

    /**
     * Builds the uri
     */
    protected function buildUrl() {
        $this->url = $this->basepath . $this->route->path;
        $host = $this->route->host;

        if(!$host) {
            $host = $this->request->getUri()->getHost();
        }

        $this->url = '//' . $host . $this->url;
        $secure = $this->route->secure;

        //This cannot be happen
        //@codeCoverageIgnoreStart
        if($secure === NULL) {
            $secure = $this->request->isSecure();
        }
        //@codeCoverageIgnoreEnd

        $protocol = $secure ? 'https:' : 'http:';
        $this->url = $protocol . $this->url;
    }

    /**
     * Build the token replacement for URL
     */
    protected function buildTokenReplacements() {
        foreach($this->data as $key => $val) {
            $this->repl["{{$key}}"] = $this->encode($val);
        }
    }

    /**
     * Builds the replacements for optional arguments
     */
    protected function buildOptionalReplacements() {
        preg_match('#{/([a-z][a-zA-Z0-9_,]*)}#', $this->url, $matches);

        if(!$matches) {
            return;
        }

        $names = explode(',', $matches[1]);
        $key = $matches[0];
        $this->repl[$key] = $this->buildOptionalReplacement($names);
    }

    /**
     * Build a replacement for an optional argument
     *
     * @param array $names
     *
     * @return string
     */
    protected function buildOptionalReplacement($names) {
        $repl = '';

        foreach($names as $name) {
            if(!isset($this->data[$name])) {
                return $repl;
            }

            $repl .= '/' . $this->encode($this->data[$name]);
        }

        return $repl;
    }

    /**
     * If the route is wildcard add all the other value as wildcard parameter
     */
    protected function buildWildcardReplacement() {
        $wildcard = $this->route->wildcard;

        if($wildcard && isset($this->data[$wildcard])) {
            $this->url = rtrim($this->url, '/');

            foreach($this->data[$wildcard] as $val) {
                $this->url .= '/' . $this->encode($val);
            }
        }
    }

    /**
     * Encodes a value if needed
     *
     * @param string $val
     *
     * @return NULL|string
     */
    protected function encode($val) {
        if($this->raw) {
            return $val;
        }

        return is_scalar($val) ? rawurlencode($val) : NULL;
    }

}
