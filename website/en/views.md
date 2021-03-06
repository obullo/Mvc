
## Görünümler

Görünüm sınıfı uygulama içerisindeki html arayüzünü kontrol eden metotları içerir. Çerçeve içerisinde bu paket harici olarak kullanılır ve bunun için <a href="http://platesphp.com/v3/templates/">PlatesPhp</a> paketi tercih edilmiştir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require league/plates
```

### Görünüm servisi

`View` nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container->setFactory('html', 'Services\ViewPlatesFactory');
```

Görünüm servisi `Obullo\View\PlatesPhp` nesnesine geri döner ve bu nesne içerisindeki `render()` metodu `League\Plates\Template\Template` sınıfı render metodunu çağırır.

```php
namespace Services;

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
        $engine = new Engine(ROOT.'/'.APP.'/View');
        $engine->addFolder('Templates', ROOT.'/templates');
        $engine->loadExtension(new Asset(ROOT.'/public/'.strtolower(APP).'/', false));

        // -------------------------------------------------------------------
        // View helpers
        // -------------------------------------------------------------------
        //
        $engine->registerFunction('url', (new Url)->setRouter($container->get('router')));
        $engine->registerFunction('translate', (new Translate)->setTranslator($container->get('translator')));
        $engine->registerFunction('escapeHtml', new EscapeHtml);
        $engine->registerFunction('escapeHtmlAttr', new EscapeHtmlAttr);
        $engine->registerFunction('escapeUrl', new EscapeUrl);

        $template = new PlatesPhp($engine);
        $template->setContainer($container);

        return $template;
    }
}
```

> Görünüm yardımcı metotlarına geçerli görünüm dosyası içerisinden `$this->method()` yöntemi ile ulaşılabilir.
 
### Görünüm işleyici

#### $this->render($name, $data = null);

Kontrolör sınıfı içerisindeki `render()` metodu html çıktısı oluşturur.

```php
$html = $this->render('Welcome.phtml');
```

Bu fonksiyon kontrolör sınıfı içerisinden görünüm sınıfı `render()` metodunu çağırır.

```php
$html = $this->html->render('Welcome.phtml');
```

Elde edilen string türündeki html görünümü kontrolör sınıfı içerisinde `\Zend\Diactoros\Response\HtmlResponse` nesnesine aktarılmalıdır.

```php
return new HtmlResponse($this->render('Welcome.phtml'));
```

Görünüm dosyasına veri göndermek için render metodu ikinci parametresi kullanılır. Böylece bu veriler görünüm dosyası içerisinde yerel olarak erişilebilir hale gelir.

```php
$this->render('Welcome.phtml', ['foo' => 'bar']);
```

Örnek.

```php
namespace App\Controller;

use Obullo\Http\Controller;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function index(Request $request) : Response
    {
        return new HtmlResponse($this->render('Welcome.phtml'));
    }
}
```

### Klasörler

Eğer `App/View/users/` adlı klasörünüz var ise `/` bölü işareti ile  ilgili görünümü ilgili klasör altından çağırabilirsiniz.

```php
return new HtmlResponse($this->render('Users/Dashboard.phtml'));
```

Eğer görünüm servisi içerisinde bir klasör `addFolder()` metodu ile önceden aşağıdaki gibi başka bir dizine tanımlı ise,

```php
$engine->addFolder('View', ROOT.'/App/View/Shared');
```

klasör ismi ardından `::` karakteri ile tanımlı klasör yolu içerisindeki görünüm dosyalarınızı çağırabilirsiniz.

```php
return new HtmlResponse($this->renderView('View::_Error.phtml', $data));
```

### Şablonlar

Şablon yüklemek için tipik olarak dosyanın en üstünde `layout` metodu çağırılır.

```php
<?php $this->layout('Template.phtml') ?>

<h1>User Profile</h1>
<p>Hello, <?php echo $this->escape($name)?></p>

// Bu fonksiyon klasör grupları için de aynı işleve sahiptir.

<?php $this->layout('View::_Example.phtml') ?>
```

### Veri atamak

Bir görünüme veri atamak için `layout` fonksiyonu ikinci parametresi kullanılır.

```php
<?php $this->layout('Template.phtml', ['title' => 'User Profile']) ?>
```

### İçeriğe erişmek

Bir şablon içerisinden işlenmiş bir görünüme ulaşmak için `section()` metodu varsayılan `content` parametresi ile kullanılır.

```php
<html>
<head>
    <title><?php echo $this->escape($title)?></title>
</head>
<body>

<?php echo $this->section('content')?>

</body>
</html>
```

### Bölümler oluşturmak

Bölümler (sections) oluşturmak için `start()` metodu kullanılır. Bölümü kapatmak için ise `stop()` fonksiyonu ile bölüm kapatılmalıdır.

```php
<?php $this->start('welcome') ?>

    <h1>Welcome!</h1>
    <p>Hello <?php echo $this->escape($name)?></p>

<?php $this->stop() ?>
```

### Yardımcı Fonksiyonlar

#### $this->asset(string $url);

Uygulama kaynaklarına ait url adreslerini yaratmayı sağlar.

```php
<html>
<head>
    <title>Asset Extension Example</title>
    <link rel="stylesheet" href="<?php echo $this->asset('/css/bootstrap.css')?>" />
</head>

<body>

<img src="<?php echo $this->asset('/images/logo.png')?>">

</body>
</html>
```

#### $this->url(string $url, $params = []);

Uygulamanızda önceden `config/routes.yaml` dosyasında tanımlanmış yönlendirme türlerine göre güvenli url adresleri üretmenizi sağlar.

Aşağıdaki gibi bir yönlendirme konfigürasyonunuz olduğunu varsayalım.

```yaml

# name:
#    path: /
#    handler: App\Controller\DefaultController::index

user/:
    update:
        path: /update/<int:id>
        handler: App\Controller\UserController::update
    delete:
        path: /delete/<int:id>
        handler: App\Controller\UserController::delete
```

Url fonksiyonunu çağırdığınızda url adresleri yönlendirme konfigürasyonunda belirlenmiş türlere göre üretilir.

```php
$this->url('user/update', ['id' => 5]);  // Çıktı:  /user/update/5
$this->url('user/delete', ['id' => 5]);  // Çıktı:  /user/delete/5
```

Bu fonksiyon arka planda yönlendirme paketi `url` fonksiyonuna geri döner.

```php
$router->url($url, $params = []);
```

#### $this->escapeHtml($value);

Dinamik oluşturulan html etiketlerindeki olası tehlikeli karakterlerden kaçış için kullanılır.

```php
$this->escapeHtml($row['blog_comment']);
```

#### $this->escapeHtmlAttr($value);

Dinamik oluşturulan html niteliklerindeki olası tehlikeli karakterlerden kaçış için kullanılır.

```php
<span title=<?php echo $this->escapeHtmlAttr($output) ?>>
    What framework are you using?
</span>
```

#### $this->escapeUrl($value);

```html
<a href="http://example.com/?redirect=<?php echo $this->escapeUrl($input); ?>">Click here!</a>
```

> PlatesPhp hakkında daha fazla detay için <a href="http://platesphp.com/v3/extensions/asset/">Platesphp.com</a> adresini ziyaret edebilirsiniz.