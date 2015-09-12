<?php namespace buildr\Router\Rule;

use buildr\Http\Request\RequestInterface;
use buildr\Router\Rule\AttributeMatchingTrait;
use buildr\Router\Route\Route;
use buildr\Router\Rule\RuleInterface;

/**
 * Host rule
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage 
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class HostRule implements RuleInterface {

    /**
     * @type \buildr\Router\Route\Route
     */
    protected $route;

    /**
     * @type string
     */
    protected $regex;

    use AttributeMatchingTrait;

    /**
     * Check if the Request matches the Route.
     *
     * @param \buildr\Http\Request\RequestInterface $request The HTTP request.
     * @param \buildr\Router\Route\Route $route The route.
     *
     * @return bool True on success, false on failure.
     */
    public function __invoke(RequestInterface $request, Route $route) {
        if (!$route->host) {
            return TRUE;
        }

        $match = preg_match(
            $this->buildRegex($route),
            $request->getUri()->getHost(),
            $matches
        );

        if (!$match) {
            return FALSE;
        }

        $route->attributes($this->getAttributes($matches));
        return TRUE;
    }

    /**
     * Gets the attributes out of the regex matches
     *
     * @param array $matches The regex matches
     *
     * @return array
     */
    protected function getAttributes($matches) {
        $attributes = [];

        foreach($matches as $key => $val) {
            if(is_string($key)) {
                $attributes[$key] = $val;
            }
        }

        return $attributes;
    }

    /**
     * Builds the regular expression for the route host.
     *
     * @param Route $route The Route
     *
     * @return string
     */
    protected function buildRegex(Route $route) {
        $this->route = $route;
        $this->regex = str_replace('.', '\\.', $this->route->host);
        $this->setRegexAttributes();
        $this->regex = '#^' . $this->regex . '$#';

        return $this->regex;
    }

}
