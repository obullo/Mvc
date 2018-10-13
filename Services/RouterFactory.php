<?php

namespace Services;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Obullo\Router\{
    RouteCollection,
    RequestContext,
    Builder,
    Router
};
use Obullo\Router\Types\{
    StrType,
    IntType,
    TranslationType
};
class RouterFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $types = [
            new IntType('<int:id>'),
            new IntType('<int:page>'),
            new StrType('<str:name>'),
            new TranslationType('<locale:locale>'),
        ];
        $context = new RequestContext;
        $context->fromRequest($container->get('request'));
         
        $collection = new RouteCollection(['types' => $types]);
        $collection->setContext($context);

        $builder = new Builder($collection);
        $routes = $container
            ->get('loader')
            ->load(ROOT, '/config/routes.yaml')
            ->toArray();
            
        $collection = $builder->build($routes);

        return new Router($collection);
    }
}