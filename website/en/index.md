
# Obullo / Framework

[![Build Status](https://travis-ci.org/obullo/Framework.svg?branch=master)](https://travis-ci.org/obullo/Framework)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/obullo/framework.svg)](https://packagist.org/packages/obullo/framework)

> Obullo ve Zend bileşenleri ile mvc uygulamaları oluşturun.

## Proje yaratmak

``` bash
$ composer create project obullo/skeleton
```

## Kurulum

``` bash
$ composer update
```

## Gereksinimler

Bu versiyon aşağıdaki PHP sürümlerini destekliyor.

* 7.0
* 7.1
* 7.2

## Testler

``` bash
$ vendor/bin/phpunit
```

## Hızlı başlangıç

Kök dizindeki `public/index.php` dosyasına göz atın.

```php
require '../../vendor/autoload.php';

define('ROOT', dirname(dirname(__DIR__)));
define('APP', 'App');

use Obullo\Http\Application;
use Zend\ServiceManager\ServiceManager;
use Dotenv\Dotenv;
```

Ortam Yöneticisi

```php
if (false == isset($_SERVER['APP_ENV'])) {
    (new Dotenv(ROOT))->load();
}
$env = $_SERVER['APP_ENV'] ?? 'dev';

error_reporting(0);
if ('prod' !== $env) {
    ini_set('display_errors', 1);  
    error_reporting(E_ALL);
}
```

Servis Yöneticisi

```php
$container = new ServiceManager;
$container->setFactory('request', 'Services\RequestFactory');
$container->setFactory('loader', 'Services\LoaderFactory');
$container->setFactory('router', 'Services\RouterFactory');
$container->setFactory('translator', 'Services\TranslatorFactory');
$container->setFactory('events', 'Services\EventManagerFactory');
$container->setFactory('session', 'Services\SessionFactory');
$container->setFactory('adapter', 'Services\ZendDbFactory');
$container->setFactory('view', 'Services\ViewPlatesFactory');
$container->setFactory('logger', 'Services\LoggerFactory');
$container->setFactory('cookie', 'Services\CookieFactory');
$container->setFactory('flash', 'Services\FlashMessengerFactory');
$container->setFactory('error', 'Services\ErrorHandlerFactory');
$container->setFactory('escaper', 'Services\EscaperFactory');
```

Üst seviye kütüphaneler

$events  = $container->get('events');
$request = $container->get('request');
$session = $container->get('session');


Hata Kontrolü

```php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
```

İstisnai Hata Kontrolü

```php
set_exception_handler(array($container->get('error'), 'handle'));
```

Olay Dinleyiciler

```php
$listeners = [
    'App\Event\ErrorListener',
    'App\Event\RouteListener',
    'App\Event\HttpMethodListener',
    'App\Event\SendResponseListener',
];
foreach ($listeners as $listener) { // Create listeners
    $object = new $listener;
    if ($object instanceof ContainerAwareInterface) {
        $object->setContainer($container);
    }
    $object->attach($events);
}
```

Katmanlar

```php
$queue = [
    new App\Middleware\HttpMethod
];
$stack = new Stack;
$stack->setContainer($container);
foreach ($queue as $value) {
    $stack = $stack->withMiddleware($value);
}
```

Çekirdek

```php
$kernel = new Kernel($events, $container->get('router'), new ControllerResolver, $stack, new ArgumentResolver);
$kernel->setContainer($container)
```

Yanıt Gönderici

```php
$response = $kernel->handle($request);
$kernel->send($response);
```

## Servisler

[Services.md](services.md)

## Yönlendirmeler

[Router.md](router.md)

## Konfigürasyon

[Config.md](config.md)

## Kontrolör

[Controller.md](controller.md)

## Http

[Http.md](http.md)

## Katmanlar

[Middlewares.md](middlewares.md)

## Hatalar

[Errors.md](errors.md)

## Çerezler

[Cookies.md](cookies.md)

## Loglama

[Logger.md](logger.md)

## Olaylar

[Events.md](events.md)

## Oturumlar

[Sessions.md](sessions.md)

## Görünümler

[Views.md](views.md)

## Veritabanı

[Database.md](database.md)

## Konsol

[Console.md](console.md)

## Çoklu Dil Desteği

[Translation.md](translation.md)