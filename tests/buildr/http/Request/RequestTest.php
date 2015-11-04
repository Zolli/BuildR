<?php namespace buildr\tests\Http\Request; 

use buildr\Http\Request\Method\HttpRequestMethod;
use buildr\Http\Request\Request;
use buildr\Http\Request\Parameter\QueryParameter;
use buildr\Http\Request\Parameter\PostParameter;
use buildr\Http\Request\Parameter\HeaderParameter;
use buildr\Http\Request\Parameter\GlobalParameter;
use buildr\Http\Header\HeaderBag;
use buildr\Http\Uri\Uri;
use \buildr\tests\Buildr_TestCase as BuildRTestCase;

/**
 * Request test case
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Tests\Http\Request
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class RequestTest extends BuildRTestCase {

    /**
     * @type \buildr\Http\Request\Request
     */
    private $request;

    public function setUp() {
        $this->request = new Request();

        $dummyData = self::getDefaultData();

        $this->request->createFromGlobals(
            $dummyData['server'],
            $dummyData['cookie'],
            $dummyData['query'],
            $dummyData['post']);

        parent::setUp();
    }

    public function testPostParameters() {
        //All getters
        $this->assertCount(2, $this->request->getAllPostParam());
        $this->assertEquals(self::getDefaultData()['post'], $this->request->getAllPostParam());

        //*Has methods
        $this->assertTrue($this->request->hasPostParam('param'));
        $this->assertFalse($this->request->hasPostParam('unexpectedParam'));

        //Getters
        $this->assertEquals('value', $this->request->getPostParam('param')->asString());
        $this->assertInstanceOf(PostParameter::class, $this->request->getPostParam('param'));
        $this->assertEquals('defaultParam', $this->request->getPostParam('nonExistingValue', 'defaultParam'));
    }

    public function testQueryParameters() {
        //All getters
        $this->assertCount(1, $this->request->getAllQueryParam());
        $this->assertEquals(self::getDefaultData()['query'], $this->request->getAllQueryParam());

        //*Has methods
        $this->assertTrue($this->request->hasQueryParam('a'));
        $this->assertFalse($this->request->hasQueryParam('unexpectedParam'));

        //Getters
        $this->assertEquals('b', $this->request->getQueryParam('a')->asString());
        $this->assertInstanceOf(QueryParameter::class, $this->request->getQueryParam('a'));
        $this->assertEquals('defaultParam', $this->request->getQueryParam('nonExistingValue', 'defaultParam'));
    }

    public function testCookies() {
        //All getters
        $this->assertCount(1, $this->request->getAllCookie());
        $this->assertEquals(self::getDefaultData()['cookie'], $this->request->getAllCookie());

        //*Has methods
        $this->assertTrue($this->request->hasCookie('testCookie'));
        $this->assertFalse($this->request->hasCookie('nonExistCookie'));

        //Getters
        $this->assertInstanceOf(\stdClass::class, unserialize($this->request->getCookie('testCookie')));
        $this->assertEquals('defaultParam', $this->request->getCookie('nonExistCookie', 'defaultParam'));
    }

    public function testHeaders() {
        //All getters
        $this->assertCount(2, $this->request->getAllHeaders());

        //*Has methods
        $this->assertTrue($this->request->hasHeader('Host'));
        $this->assertFalse($this->request->hasHeader('SERVER_PORT'));

        //Getters
        $this->assertInstanceOf(HeaderParameter::class, $this->request->getHeader('Host'));
        $this->assertEquals('defaultHeader', $this->request->getHeader('nonExistHeader', 'defaultHeader'));

        //Header bag
        $this->assertInstanceOf(HeaderBag::class, $this->request->getHeaderBag());
    }

    public function testGlobals() {
        //All getters
        $this->assertCount(5, $this->request->getAllGlobal());
        $this->assertEquals(self::getDefaultData()['server'], $this->request->getAllGlobal());

        //*Has methods
        $this->assertTrue($this->request->hasGlobal('SERVER_PORT'));
        $this->assertFalse($this->request->hasGlobal('nonExistGlobal'));

        //Getters
        $this->assertEquals(80, $this->request->getGlobal('SERVER_PORT')->asInt());
        $this->assertInstanceOf(GlobalParameter::class, $this->request->getGlobal('SERVER_PORT'));
        $this->assertEquals('defaultParam', $this->request->getGlobal('nonExistGlobal', 'defaultParam'));
    }

    public function testItReturnsTheCorrectUriObject() {
        $this->assertInstanceOf(Uri::class, $this->request->getUri());
    }

    public function testItReturnsSecureIndicatorAsBoolean() {
        $this->assertTrue(is_bool($this->request->isSecure()));
    }

    public function testRequestMethodDetectionWorkingCorrectly() {
        $this->assertTrue($this->request->is(HttpRequestMethod::GET()));
        $this->assertFalse($this->request->is(HttpRequestMethod::CONNECT()));
    }

    public function testItReturnsTheInputStreamAsResource() {
        $this->assertTrue(is_resource($this->request->getInputStream()));
    }

    public function testHeaderDetection() {
        $closure = $this->getClosureForMethod(Request::class, 'collectHeaders');
        $defaults = self::getDefaultData()['server'];

        //Defaults
        $resultDefaults = call_user_func_array($closure, [$defaults]);
        $expectationDefault = ['Host' => 'http://test.tld', 'Https' => 1];

        $this->assertCount(2, $resultDefaults);
        $this->assertEquals($expectationDefault, $resultDefaults);

        $withContents = $defaults;
        $withContents['CONTENT_TYPE'] = 'text/html';

        $expectationWithContent = $expectationDefault;
        $expectationWithContent['Content-Type'] = 'text/html';

        $resultWithContent = call_user_func_array($closure, [$withContents]);

        $this->assertCount(3, $resultWithContent);
        $this->assertEquals($expectationWithContent, $resultWithContent);
    }

    public static function getDefaultData() {
        return [
            'server' => [
                'HTTP_HOST' => 'http://test.tld',
                'HTTP_HTTPS' => 1,
                'HTTP_COOKIE' => 'dummyCookie',
                'SERVER_PORT' => 80,
                'REQUEST_SCHEME' => 'https',
            ],
            'cookie' => [
                'testCookie' => serialize(new \stdClass()),
            ],
            'query' => [
                'a' => 'b',
            ],
            'post' => [
                'param' => 'value',
                'anotherParam' => 'anotherValue',
            ],
        ];
    }

}
