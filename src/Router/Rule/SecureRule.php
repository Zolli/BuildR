<?php namespace buildr\Router\Rule;

use buildr\Http\Request\RequestInterface;
use buildr\Router\Route\Route;
use buildr\Router\Rule\RuleInterface;

/**
 * Secure rule
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
class SecureRule implements RuleInterface {

    /**
     * Check if the Request matches the Route.
     *
     * @param \buildr\Http\Request\RequestInterface $request The HTTP request.
     * @param \buildr\Router\Route\Route $route The route.
     *
     * @return bool True on success, false on failure.
     */
    public function __invoke(RequestInterface $request, Route $route) {
       return !$request->isSecure();
    }

}
