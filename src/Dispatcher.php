<?php

namespace Obullo\Mvc;

use ReflectionClass;
use Obullo\Router\Router;
use Obullo\Mvc\Dependency\Resolver;
use Obullo\Mvc\Container\ContainerAwareTrait;

/**
 * Application dispatcher
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Dispatcher
{
    use ContainerAwareTrait;

    protected $name;
    protected $class;
    protected $router;
    protected $method;
    protected $methods = array();
    protected $classInstance;
    protected $isCallable = false;

    /**
     * Constructor
     * 
     * @param Router $router router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->name = 'App';
    }

    /**
     * Dispatch request
     * 
     * @return void
     */
    public function dispatch()
    {
        if (false == $this->router->hasMatch()) {
            return;
        }
        $handler = $this->router->getMatchedRoute()
            ->getHandler();
        if (is_callable($handler)) {
            $this->isCallable = true;
            $this->resolveHandler($handler);
        }
    }

    /**
     * Resolve handler
     * 
     * @param  string $handler handler
     * @return void
     */
    protected function resolveHandler(string $handler)
    {        
        $container = $this->getContainer();
        $explodeMethod = explode('::', $handler);
        $this->class  = $explodeMethod[0];
        $this->method = $explodeMethod[1];
        $explode  = explode('\\', $this->class);
        $this->name = (string)$explode[0];
        $reflection = new ReflectionClass($this->class);
        $this->classInstance = $reflection->newInstanceWithoutConstructor();
        $this->methods = get_class_methods($this->classInstance);
        
        $container->setFactory('middleware', function(){
            return new \Obullo\Mvc\Middleware($this);
        });
        $this->classInstance->setContainer($container);
        if ($reflection->hasMethod('__construct')) {
            $this->classInstance->__construct();
        }
    }

    /**
     * Returns to first namespace e.g. 'App'.
     * 
     * @return string
     */
    public function getFirstNamespace() : string
    {
        return $this->name;
    }

    /**
     * Returns to is callable
     * 
     * @return string
     */
    public function getClassIsCallable() : bool
    {
        return $this->isCallable;
    }

    /**
     * Returns to resolved class name
     * 
     * @return string
     */
    public function getClassName() : string
    {
        return $this->class;
    }

    /**
     * Returns to resolved class method
     * 
     * @return string
     */
    public function getClassMethod() : string
    {
        return $this->method;
    }

    /**
     * Returns to resolved class methods
     * 
     * @return string
     */
    public function getClassMethods() : array
    {
        return $this->methods;
    }

    /**
     * Returns to resolved controller instance
     * 
     * @return object
     */
    public function getClassInstance()
    {
        return $this->classInstance;
    }
}