<?php namespace buildr\tests\loader;

use buildr\Loader\ClassLoader;
use buildr\Loader\ClassMapClassLoader;
use buildr\tests\Buildr_TestCase as BuilderTestCase;

/**
 * BuildR - PHP based continuous integration server
 *
 * Serviceprovider tests
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage tests\loader
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class classLoaderTest extends BuilderTestCase {

    /**
     * @type \buildr\Loader\ClassLoader
     */
    private $loader = NULL;

    protected function setUp() {
        $this->loader = new ClassLoader();

        parent::setUp(); // TODO: Change the autogenerated stub
    }


    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testItGenerateErrorOnNotProperLoader() {
        $this->loader->registerLoader(new \stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not found any class Loader for priority 5!
     */
    public function testItThrowsExceptionOnUnknownPriorityGet() {
        $this->loader->getLoaderPriority(5);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not found any class Loader, tagged with "unknownLoader" name!
     */
    public function testItThrowsExceptionOnUnknownLoaderNameGet() {
        $this->loader->getLoaderByName("unknownLoader");
    }

    public function testItReturnsTheProperLoaderByName() {
        $this->loader->registerLoader(new ClassMapClassLoader());

        $returnedLoaders = $this->loader->getLoaderByName("classMapClassLoader");

        $this->assertArrayHasKey(0, $returnedLoaders);
        $this->assertCount(1, $returnedLoaders);
    }

    public function testItReturnsTheProperLoadersByName() {
        set_error_handler(function($code, $string) {});

        $this->loader->registerLoader(new ClassMapClassLoader());
        $this->loader->registerLoader(new ClassMapClassLoader());

        $returnedLoaders = $this->loader->getLoaderByName("classMapClassLoader");

        $this->assertCount(2, $returnedLoaders);
    }

    public function testItReturnsTheProperLoaderByPriority() {
        $this->loader->registerLoader(new ClassMapClassLoader());

        $returnedLoader = $this->loader->getLoaderPriority(2);
        $this->assertInstanceOf(ClassMapClassLoader::class, $returnedLoader);
    }

    public function testItReturnsTheRegisteredLoadersProperly() {
        $this->assertCount(0, $this->loader->getLoaders());

        $this->loader->registerLoader(new ClassMapClassLoader());

        $this->assertCount(1, $this->loader->getLoaders());
    }

    public function testItIncreaseThePriorityProperly() {
        $classMapLoaderOne = new ClassMapClassLoader();
        $classMapLoaderTwo = new ClassMapClassLoader();

        set_error_handler(function($errorNumber, $errorString) {
            $this->assertEquals(E_USER_NOTICE, $errorNumber);
            $this->assertEquals("Another class Loader is registered with priority 2! Increasing priority by one, to find a new spot.", $errorString);
        });

        $this->loader->registerLoader($classMapLoaderOne);
        $this->loader->registerLoader($classMapLoaderTwo);

        $this->assertEquals(3, $classMapLoaderTwo->getPriority());
    }

}