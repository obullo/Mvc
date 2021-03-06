<?php

namespace Tests\App\Services;

use Obullo\View\Helper;
use Obullo\View\PlatesPhp;
use League\Plates\Engine;
use League\Plates\Extension\Asset;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ViewPlatesFactory implements FactoryInterface
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
        $engine = new Engine(ROOT.'/tests/App/View');
        $engine->setFileExtension('phtml');
        $engine->addFolder('templates', ROOT.'/tests/var/templates');
        // $engine->loadExtension(new Asset(ROOT.'/public/'.strtolower(APP).'/', false));

        // -------------------------------------------------------------------
        // View helpers
        // -------------------------------------------------------------------
        //
        $engine->registerFunction('url', new Helper\Url($container));
        $engine->registerFunction('escapeHtml', new Helper\EscapeHtml);
        $engine->registerFunction('escapeHtmlAttr', new Helper\EscapeHtmlAttr);
        $engine->registerFunction('escapeCss', new Helper\EscapeCss);
        $engine->registerFunction('escapeJs', new Helper\EscapeJs);
        $engine->registerFunction('escapeUrl', new Helper\EscapeUrl);

        $template = new PlatesPhp($engine);
        $template->setContainer($container);

        return $template;
    }
}