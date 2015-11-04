<?php namespace buildr\Container;

use buildr\Container\Exception\InstantiationException;
use buildr\ServiceProvider\ServiceProvider;
use buildr\ServiceProvider\ServiceProviderInterface;
use buildr\Container\Alias\AliasResolver;
use buildr\Container\Method\ContainerMethod;
use buildr\Container\Exception\NotFoundException;
use buildr\Container\Exception\InjectionException;
use buildr\Container\Exception\CannotChangeException;
use \ReflectionClass;
use \ReflectionMethod;

/**
 * Dependency injection container implementation
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Container
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class Container implements ContainerInterface {

    /**
     * @type array
     */
    protected $keys = [];

    /**
     * @type array
     */
    protected $closures = [];

    /**
     * @type array
     */
    protected $instances = [];

    /**
     * @type array
     */
    protected $bindings = [];

    /**
     * @type array
     */
    protected $frozen = [];

    /**
     * @type array
     */
    protected $contextual = [];

    /**
     * @type \buildr\Container\Alias\AliasResolver
     */
    protected $aliasResolver;

    /**
     * @type array
     */
    private $buildStack = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->aliasResolver = new AliasResolver();

        $this->instance('container', $this)
            ->freeze('container')
            ->alias(ContainerInterface::class, 'container')
            ->freeze(ContainerInterface::class);
    }

    /**
     * Return a new contextual builder to add a new binding
     *
     * @param string $concrete
     *
     * @return \buildr\Container\ContextualBuilder
     */
    public function when($concrete) {
        return new ContextualBuilder($this, $concrete);
    }

    /**
     * Register a new contextual binding
     *
     * @param string $concrete
     * @param string $abstract
     * @param string $implementation
     *
     * @return \buildr\Container\ContainerInterface
     */
    public function contextual($concrete, $abstract, $implementation) {
        $this->contextual[$concrete][$abstract] = $implementation;

        return $this;
    }

    /**
     * Return the abstract class name when it has a contextual binding
     *
     * @param string $abstract
     *
     * @return NULL|string
     */
    protected function getContextualConcrete($abstract) {
        if (isset($this->contextual[end($this->buildStack)][$abstract])) {
            return $this->contextual[end($this->buildStack)][$abstract];
        }
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id) {
        $id = $this->aliasResolver->getOrigin($id);

        //@codeCoverageIgnoreStart
        $this->checkOptionalService($id);
        //@codeCoverageIgnoreEnd

        //Contextual binding check
        if(($concrete = $this->getContextualConcrete($id)) !== NULL) {
            return $this->construct($concrete);
        }

        //If this is not bound try to create it automatically, if unable to instantiate, we
        //throw an exception.
        if(!isset($this->keys[$id])) {
            try {
                return $this->construct($id);
            } catch (\ReflectionException $e) {
                throw new NotFoundException('The entry (' . $id . ') is not bound, and cant create it automatically!');
            }
        }

        //If this a bound entry try to resolve it
        $key = $this->keys[$id];
        $this->freeze($id);

        if(!isset($this->instances[$id])) {
            switch($key) {
                case ContainerMethod::CLOSURE:
                    $this->instances[$id] = call_user_func($this->closures[$id], $this);
                    break;
                case ContainerMethod::BIND:
                    $this->instances[$id] = $this->construct($this->bindings[$id]);
                    break;
                case ContainerMethod::WIRE:
                    $this->instances[$id] = $this->construct($this->bindings[$id]);
                    $this->inject($this->instances[$id]);
                    break;
            }
        }

        return $this->instances[$id];
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id) {
        return isset($this->keys[$id]);
    }

    /**
     * Register a new instance to the container
     *
     * @param string $id Service name
     * @param object $value Registered object
     *
     * @return \buildr\Container\ContainerInterface
     * @throws \buildr\Container\Exception\CannotChangeException
     */
    public function instance($id, $value) {
        $this->destroy($id);
        $this->keys[$id] = ContainerMethod::INSTANCE;
        $this->instances[$id] = $value;

        return $this;
    }

    /**
     * Register a new instance to the container as a closure
     *
     * @param string $id Service name
     * @param \Closure $value closure to register
     *
     * @return \buildr\Container\ContainerInterface
     * @throws \buildr\Container\Alias\CannotChangeException
     */
    public function closure($id, \Closure $value) {
        $this->destroy($id);
        $this->keys[$id] = ContainerMethod::CLOSURE;
        $this->closures[$id] = $value;

        return $this;
    }

    /**
     * Bind an interface to its concrete implementation
     *
     * @param string $id ServiceName
     * @param string|NULL $class The binded class name
     *
     * @return \buildr\Container\ContainerInterface
     * @throws \buildr\Container\Alias\CannotChangeException
     */
    public function bind($id, $class = NULL) {
        if($class === NULL) {
            $class = $id;
        }

        $this->destroy($id);
        $this->keys[$id] = ContainerMethod::BIND;
        $this->bindings[$id] = $class;

        if($id !== $class) {
            $this->alias($class, $id);
        }

        return $this;
    }

    /**
     * Register a new alias for a class
     *
     * @param string $name The alias name
     * @param string $origin The original service name
     *
     * @return \buildr\Container\ContainerInterface
     */
    public function alias($name, $origin) {
        $this->aliasResolver->alias($name, $origin);

        return $this;
    }

    /**
     * Register a class that can be used in autowireing
     *
     * @param string $id Service name
     * @param null|object $class
     *
     * @return \buildr\Container\ContainerInterface
     */
    public function wire($id, $class = NULL) {
        $this->bind($id, $class);
        $this->keys[$id] = ContainerMethod::WIRE;

        return $this;
    }

    /**
     * Freeze a service in the container, so that cant be changed or removed
     *
     * @param string $id Service or alias name
     *
     * @return \buildr\Container\ContainerInterface
     */
    public function freeze($id) {
        $this->frozen[$id] = TRUE;

        return $this;
    }

    /**
     * Destroy a service that registered in the container
     *
     * @param string $id ServiceName
     *
     * @throws \buildr\Container\Exception\CannotChangeException
     */
    public function destroy($id) {
        if(isset($this->frozen[$id])) {
            throw new CannotChangeException('Cannot destroy object that been frozen! (' . $id . ')');
        }

        unset(
            $this->keys[$id],
            $this->closures[$id],
            $this->instances[$id],
            $this->bindings[$id]
        );
    }

    /**
     * Allow to modify an object in the container. The given service is injected into a closure and
     * tha closure must return the modified object
     *
     * @param string $id Service name
     * @param \Closure $callback
     *
     * @return \buildr\Container\ContainerInterface
     * @throws \buildr\Container\Exception\NotFoundException
     */
    public function extend($id, \Closure $callback) {
        $id = $this->aliasResolver->getOrigin($id);

        if(!isset($this->keys[$id])) {
            throw new NotFoundException('The following entry not bound to container: ' . $id);
        }

        //Try to extend a singleton stored as class
        if (isset($this->instances[$id])) {
            $this->instances[$id] = call_user_func($callback, $this->instances[$id]);
        }

        //Try to extend a singleton stored as closure
        if (isset($this->closures[$id])) {
            $closure = $this->closures[$id];

            $this->closures[$id] = function () use ($closure, $callback) {
                return call_user_func($callback, call_user_func($closure, $this));
            };
        }

        return $this;
    }


    /**
     * Try to instantiate a new object from the given class and figure out
     * all its dependency through PHP Reflection API
     *
     * @param string $class The class FQCN
     * @param array $predefinedParameters Allows you to force constructor parameter
     *
     * <pre>
     * The forced constructor parameters used primary, the order of parameter does not matter
     * because its defined by variable name as the array element key.
     *
     * Example:
     *
     * public function __construct($nullParam, \Hello $world, $param = 'world', $anotherNullParam);
     *
     * In this example we want to force the 2 null parameter.
     *
     * Container::construct('Class', ['anotherNullParam' => 'string 2', 'nullParam' => 'string 1']);
     *
     * Of course, you can override parameter that can be resolved automatically, like $world
     * </pre>
     *
     * @throws \buildr\Container\Exception\InstantiationException
     *
     * @return object
     */
    public function construct($class, $predefinedParameters = []) {
        $this->buildStack[] = $class;

        $classReflector = new ReflectionClass($class);

        //If the class is an abstract class, cant create a new instance
        if(($classReflector->isAbstract())
            && ($classReflector->getModifiers() & \ReflectionClass::IS_EXPLICIT_ABSTRACT)) {
            throw new InstantiationException('Cant instantiate abstract classes! (' . $class . ')');
        }

        $constructorReflector = $classReflector->getConstructor();

        if($constructorReflector) {
            $parameters = $this->resolveClassDependencies($constructorReflector, $predefinedParameters);

            array_pop($this->buildStack);
            return $classReflector->newInstanceArgs($parameters);
        }

        array_pop($this->buildStack);
        return new $class;
    }

    /**
     * Inject the class properties automatically that can by injected and the docComment
     * contains the @Wire operator.
     *
     * This method also takes default parameters, see Constainer::construct() method for example.
     *
     * @param object $object
     * @param array $predefinedParameters
     *
     * @throws \buildr\Container\Exception\NotFoundException
     * @throws \buildr\Container\Exception\InjectionException
     */
    public function inject($object, $predefinedParameters = []) {
        $objectReflector = new \ReflectionObject($object);

        foreach($objectReflector->getProperties() as $objectProperty) {
            $comment = $objectProperty->getDocComment();

            if(strpos($comment, '@Wire') !== FALSE) {
                $className = $this->extractClassFromComment($comment);

                $objectProperty->setAccessible(TRUE);

                if(isset($predefinedParameters[$objectProperty->name])) {
                    $objectProperty->setValue($object, $predefinedParameters[$objectProperty->name]);
                } else if($className != NULL) {
                    $objectProperty->setValue($object, $this->get($className));
                } else {
                    $message = 'Unable to automatically inject class property (';
                    $message .= get_class($object) . '::' . $objectProperty->name . '). No value specified!';

                    throw (new InjectionException($message))->setValue($objectProperty->name);
                }
            }
        }
    }

    /**
     * Register a service provider in container. When provider defined interface for class
     * This alos registered as alias(es).
     *
     * @param \buildr\ServiceProvider\ServiceProviderInterface $provider
     */
    public function register(ServiceProviderInterface $provider) {
        $container = $this->get('container');
        $this->instance($provider->getBindingName(), $provider->register($container));

        if(($aliases = $provider->provides()) !== NULL) {
            foreach($aliases as $alias) {
                $this->alias($alias, $provider->getBindingName());
                $this->freeze($alias);
            }
        }
    }

    /**
     * Extract the class name from a property docBlock. This method able to detect class
     * name defined with @var or @type, and remove the trailing slash from the beginning if is defined.
     *
     * @param string $comment
     *
     * @return null|string
     */
    protected function extractClassFromComment($comment) {
        $position = strpos($comment, '@type');
        $foundIndicator = '@type';

        if($position === FALSE) {
            $position = strpos($comment, '@var');
            $foundIndicator = '@var';
        }

        if($position === FALSE) {
            return NULL;
        }

        preg_match('/^([a-zA-Z0-9\\\\]+)/', ltrim(substr($comment, $position + strlen($foundIndicator))), $matches);
        $className = $matches[0];

        if($className[0] == '\\') {
            $className = substr($className, 1);
        }

        return $className;
    }

    /**
     * Resolves a method dependencies and return as an array.
     *
     * @param \ReflectionMethod $constructor
     * @param array $parameters
     *
     * @return array
     * @throws \buildr\Container\Exception\NotFoundException
     */
    protected function resolveClassDependencies(ReflectionMethod $constructor, $parameters) {
        $returnedParams = [];

        $constructorParams = $constructor->getParameters();

        foreach($constructorParams as $param) {
            //If the parameter is pre-defined put it into the returned array and continue the loop
            if(isset($parameters[$param->name])) {
                $returnedParams[] = $parameters[$param->name];

                continue;
            }

            $paramClass = $param->getClass();

            if($paramClass !== NULL) {
                $returnedParams[] = $this->get($paramClass->name, []);
            } else if($param->isDefaultValueAvailable()) {
                $returnedParams[] = $param->getDefaultValue();
            } else {
                $returnedParams[] = NULL;
            }
        }

        return $returnedParams;
    }

    /**
     * Check for the given service is registered as optional provider. If it is, and is not loaded already
     * register this optional provider first
     *
     * @param string $id
     *
     * @codeCoverageIgnore
     */
    private function checkOptionalService($id) {
        if(ServiceProvider::isOptionalService($id) && !ServiceProvider::isOptionalServiceLoaded($id)) {
            ServiceProvider::loadOptionalService($id);
        }
    }

    /**
     * Whether a offset exists
     *
     * @return boolean
     */
    public function offsetExists($offset) {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @return object
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     */
    public function offsetSet($offset, $value) {
        $this->instance($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        $this->destroy($offset);
    }

    /**
     * Deprecated proxy method for previous version of the container
     *
     * @param string $id Service name
     * @param $service Actual service
     *
     * @deprecated
     * @codeCoverageIgnore
     */
    public function add($id, $service) {
        $this->instance($id, $service);
    }

}
