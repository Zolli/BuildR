<?php namespace buildr\Router\Rule;

use buildr\Http\Request\RequestInterface;
use buildr\Router\Route\Route;
use buildr\Router\Rule\RuleInterface;

/**
 * Accept rule
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Router\Rule
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class AcceptRule implements RuleInterface {

    /**
     * Check if the Request matches the Route.
     *
     * @param \buildr\Http\Request\RequestInterface $request The HTTP request.
     * @param \buildr\Router\Route\Route $route The route.
     *
     * @return bool True on success, false on failure.
     */
    public function __invoke(RequestInterface $request, Route $route) {
        if(!$route->accepts) {
            return TRUE;
        }

        $requestAccepts = $request->getHeader('Accept')->asString();

        if(!$requestAccepts) {
            return TRUE;
        }

        return $this->matches($route->accepts, $requestAccepts);
    }

    /**
     * Does what the route accepts match what the request accepts
     *
     * @param array $routeAccepts What the route accepts
     * @param array $requestAccepts What the request accepts
     *
     * @return bool
     */
    protected function matches($routeAccepts, $requestAccepts) {
        $requestAccepts = implode(';', $requestAccepts);

        if($this->match('*/*', $requestAccepts)) {
            return TRUE;
        }

        foreach ($routeAccepts as $type) {
            if($this->match($type, $requestAccepts)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Match the accept() value to the Accept header
     *
     * @param string $type The Route accept type
     * @param string $header The Request accept header
     *
     * @return bool True on a match, false if not
     */
    protected function match($type, $header) {
        list($type, $subtype) = explode('/', $type);
        $type = preg_quote($type);
        $subtype = preg_quote($subtype);
        $regex = "#$type/($subtype|\*)(;q=(\d\.\d))?#";
        $found = preg_match($regex, $header, $matches);

        if(!$found) {
            return FALSE;
        }

        if(isset($matches[3])) {
            return $matches[3] !== '0.0';
        }

        return TRUE;
    }

}
