<?php namespace buildr\tests\router;

use buildr\Application\Application;
use buildr\Http\Request\Request;
use buildr\Router\Route\Route;
use buildr\Router\Router;
use buildr\tests\Buildr_TestCase as BuildrTestCase;
use buildr\Router\Map\RouteMap;
use buildr\Router\Matcher\RouteMatcher;
use buildr\Router\Generator\UrlGenerator;
use buildr\tests\Http\Request\RequestTest;
use buildr\Router\Exception\ImmutablePropertyException;

class routerTest extends BuildrTestCase {

    /**
     * @type \buildr\Router\Router
     */
    private $router;

    protected function setUp() {
        $this->router = new Router();

        parent::setUp();
    }

    /**
     * @return \buildr\Router\Route\Route
     */
    private function getDummyRoute() {
        $map = $this->router->getMap();
        $map->get('test', '/test', function() {});

        return $map->getRoute('test');
    }

    /**
     * @expectedException \buildr\Router\Exception\ImmutablePropertyException
     * @expectedExceptionMessage buildr\Router\Route\Route::$path is immutable
     */
    public function testRouteThrowsExceptionWhenTryToChangeThePath() {
        $route = $this->getDummyRoute();

        $route->path('test_value');
    }

    /**
     * @expectedException \buildr\Router\Exception\ImmutablePropertyException
     * @expectedExceptionMessage buildr\Router\Route\Route::$name is immutable
     */
    public function testRouteThrowsExceptionWhenTryToChangeTheName() {
        $route = $this->getDummyRoute();

        $route->name('test_value');
    }

    /**
     * @expectedException \buildr\Router\Exception\RouteAlreadyExistException
     * @expectedExceptionMessage The route (test) is already exist
     */
    public function testItThrowsExceptionWhenTryToRegisterRouteWithSameName() {
        $map = $this->router->getMap();

        $map->get('test', '/test', function() {});
        $map->get('test', '/test', function() {});
    }

    public function testItCreatesTheDefaultFactories() {
        $routeFactory = $this->getPrivatePropertyFromClass(Router::class, 'routeFactory', $this->router);
        $mapFactory = $this->getPrivatePropertyFromClass(Router::class, 'mapFactory', $this->router);

        $this->assertTrue(is_callable($routeFactory));
        $this->assertTrue(is_callable($mapFactory));

        $routeFactoryResult = call_user_func($routeFactory);
        $mapFactoryResult = call_user_func($mapFactory);

        $this->assertInstanceOf(Route::class, $routeFactoryResult);
        $this->assertInstanceOf(RouteMap::class, $mapFactoryResult);
    }

    public function testFailedRouteHandling() {
        $this->assertFalse($this->router->hasFailedHandler());

        //Register a dummy route, used for fail handling
        $this->router->getMap()->get('error', '/error', function() {});

        $this->router->setFailedHandlerName('error');

        $this->assertTrue($this->router->hasFailedHandler());

        $failHandler = $this->router->getFailedHandlerRoute();

        $this->assertInstanceOf(Route::class, $failHandler);
        $this->assertEquals('error', $failHandler->name);
    }

    public function testItReturnsACorrectMatcher() {
        $matcher = $this->router->getMatcher();
        $this->assertInstanceOf(RouteMatcher::class, $matcher);
    }

    public function testItReturnsACorrectGenerator() {
        $request = new Request();
        $dummyData = RequestTest::getDefaultData();
        $container = Application::getContainer();

        $request->createFromGlobals(
            $dummyData['server'],
            $dummyData['cookie'],
            $dummyData['query'],
            $dummyData['post']);

        $container['request'] = $request;

        $generator = $this->router->getGenerator();
        $this->assertInstanceOf(UrlGenerator::class, $generator);
    }

    public function testRouteFunctionality() {
        $route = $this->getDummyRoute();

        //Accept
        $route->accepts('application/json');
        $this->assertEquals(['application/json'], $route->accepts);

        //Allows
        $route->allows('DELETE');
        $this->assertEquals(['GET', 'DELETE'], $route->allows);

        //Attributes
        $route->attributes(['test' => 'value', 'key' => 'value2']);
        $this->assertEquals(['test' => 'value', 'key' => 'value2'], $route->attributes);

        //Defaults
        $route->defaults(['articleId' => 10]);
        $this->assertEquals(['articleId' => 10], $route->defaults);

        //Extras
        $route->extras(['test' => 'extraContent']);
        $this->assertEquals(['test' => 'extraContent'], $route->extras);

        //Handler
        $this->assertTrue(is_callable($route->handler));

        //Host
        $route->host(['dummy.tld', 'buildr-framework.io']);
        $this->assertEquals(['dummy.tld', 'buildr-framework.io'], $route->host);

        //Routable
        $this->assertTrue($route->isRouteable);
        $route->isRouteable(FALSE);
        $this->assertFalse($route->isRouteable);

        //Name
        $this->assertEquals('test', $route->name);

        //Name prefix
        $this->assertNull($route->namePrefix);
        $route->namePrefix('/');
        $this->assertEquals('/', $route->namePrefix);

        //Path
        $this->assertEquals('/test', $route->path);

        //Path prefix
        $route->pathPrefix('/asd');
        $this->assertEquals('/asd', $route->pathPrefix);

        //Secure
        $this->assertFalse($route->secure);
        $route->secure();
        $this->assertTrue($route->secure);

        //Tokens
        $route->tokens(['articleId' => '/a-zZ-A0-9.*/']);
        $this->assertEquals(['articleId' => '/a-zZ-A0-9.*/'], $route->tokens);

        //Wildcard
        $route->wildcard('wildcardToken');
        $this->assertEquals('wildcardToken', $route->wildcard);

        //Middleware
        $route->middleware([function() {}]);
        $this->assertCount(1, $route->middlewares);
        $route->middleware([function() {}]);
        $this->assertCount(2, $route->middlewares);
        $route->middleware([function() {}], TRUE);
        $this->assertCount(1, $route->middlewares);
    }

    public function testRouteMatcherWorksCorrectly() {
        $map = $this->router->getMap();
        $request = new Request();
        $dummyData = RequestTest::getDefaultData();
        $matcher = $this->router->getMatcher();

        $request->createFromGlobals(
            $dummyData['server'],
            $dummyData['cookie'],
            $dummyData['query'],
            $dummyData['post']);

        //Test with only non-routeable routes
        $map->get('noRouteable', '/test', function() {})->isRouteable(FALSE);
        $this->assertFalse($matcher->match($request));
        $this->assertFalse($matcher->getMatchedRoute());
        $this->assertNull($matcher->getFailedRoute());

        //Test with rout that not match
        $map->get('notMatch', '/route', function() {});
        $this->assertFalse($matcher->match($request));
        $this->assertFalse($matcher->getMatchedRoute());
        $this->assertInstanceOf(Route::class, $matcher->getFailedRoute());

        //Test with route that matches the request
        $map->get('root', '/', function() {});
        $this->assertInstanceOf(Route::class, $matcher->match($request));
        $this->assertInstanceOf(Route::class, $matcher->getFailedRoute());  //Failed route exist when route is matched
        $this->assertInstanceOf(Route::class, $matcher->getMatchedRoute());
    }

    public function testRouteMapWorksCorrectly() {
        $map = $this->router->getMap();

        //Unnamed route
        $map->route(NULL, '/test', function() {});

        //Route loading and saving
        $this->assertCount(1, $map->getRoutes());
        $existingRoutes = $map->getRoutes();
        $map->setRoutes([]);
        $this->assertCount(0, $map->getRoutes());
        $map->setRoutes($existingRoutes);
        $this->assertCount(1, $map->getRoutes());

        //Attaching
        $map->attach('articles', '/articles', function(RouteMap $map) {});
    }


}
