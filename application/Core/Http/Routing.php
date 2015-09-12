<?php namespace buildr\Application\Core\Http;

use buildr\Application\Application;
use buildr\Contract\Application\ApplicationRoutingContract;
use buildr\Http\Constants\HttpResponseCode;
use buildr\Http\Response\Facade\Response;
use buildr\Router\Route\Route;
use buildr\Router\Router;
use buildr\Router\Rule\AcceptRule;
use buildr\Router\Rule\AllowRule;
use buildr\Http\Response\ContentType\HtmlContentType;


/**
 * Routing registry for application
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Application\Core\Http
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class Routing implements ApplicationRoutingContract {

    /**
     * This function is called during the initialization. Inside this
     * function you can register all the routes that you application need.
     *
     * @param \buildr\Router\Router $router
     */
    public function register(Router $router) {
        $router->setFailedHandlerName('errorHandler');
        $map = $router->getMap();

        /**
         * Example error handling, this is not for production, only example
         */
        $map->get('errorHandler', 'error', function(Route $route) use($router) {
            $failed = $router->getMatcher()->getFailedRoute();
            /** @type \buildr\Http\Response\ResponseInterface $response */
            $response = Application::getContainer()->get('response');

            switch($failed->failedRule) {
                case AcceptRule::class:
                    $response->setStatusCode(HttpResponseCode::HTTP_NOT_ACCEPTABLE());
                    $response->setBody('Not Acceptable');
                    break;
                case AllowRule::class:
                    $response->setStatusCode(HttpResponseCode::HTTP_METHOD_NOT_ALLOWED());
                    $response->setBody('Method not Allowed, only: ' . implode(', ', $failed->allows));
                    break;
                default:
                    $response->setBody('Not Found');
                    break;
            }

            $response->setContentType(new HtmlContentType());

            return $response;

        })->isRouteable(FALSE);

        $map->get('root', '/', function() {
            return Response::html('Hello World!');
        });
    }

}
