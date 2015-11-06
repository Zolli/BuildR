<?php namespace buildr\tests\router;

use buildr\tests\Buildr_TestCase as BuildrTestCase;
use buildr\Application\Application;
use buildr\Http\Request\Request;
use buildr\Router\Route\Route;
use buildr\Router\Router;
use buildr\Router\Map\RouteMap;
use buildr\Router\Matcher\RouteMatcher;
use buildr\Router\Generator\UrlGenerator;
use buildr\tests\Http\Request\RequestTest;

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

        //Route prototype changes
        $map->extras(['key' => 'value']);
        $prototype = $this->getPrivatePropertyFromClass(RouteMap::class, 'routePrototype', $map);
        $this->assertEquals(['key' => 'value'], $prototype->extras);
    }

    public function testUrlGeneratorWorksCorrectly() {
        $map = $this->router->getMap();
        $generator = $this->router->getGenerator();

        $map->get('article.read', '/article/{id}{format}{/version,index}', function() {})
            ->tokens([
                'id' => '\d+',
                'format' => '(\.[^/]+)?',
                'version' => '\d{2}',
                'index' => '\d{2}',
            ])
            ->defaults([
                'format' => '.html',
            ]);

        $map->get('wildcard', '/read', function() {})
            ->wildcard('attributes');

        $map->get('contact', '/contact', function() {})
            ->secure(TRUE);

        $map->get('colleague', '/colleague/{name}', function() {})
            ->tokens([
                'name' => '\.{8,16}',
            ]);

        //Simple
        $urlSimple = $generator->generate('article.read', ['id' => 15, 'version' => 2]);
        $this->assertEquals('http://test.tld/article/15.html/2', $urlSimple);

        //Simple with secure
        $urlSimpleSecure = $generator->generate('contact');
        $this->assertEquals('https://test.tld/contact', $urlSimpleSecure);

        //Wildcard route
        $urlWildcard = $generator->generate('wildcard', ['attributes' => ['test', 'with', 'many', 'params']]);
        $this->assertEquals('http://test.tld/read/test/with/many/params', $urlWildcard);

        //Complex with, and without encoding
        $urlComplexEncoded = $generator->generate('colleague', ['name' => 'Péter Doe']);
        $urlComplexRaw = $generator->generateRaw('colleague', ['name' => 'Péter Doe']);
        $this->assertEquals('http://test.tld/colleague/Péter Doe', $urlComplexRaw);
        $this->assertEquals('http://test.tld/colleague/P%C3%A9ter%20Doe', $urlComplexEncoded);
    }


}
