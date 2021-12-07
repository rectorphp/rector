# 6 Rules Overview

## AppUsesStaticCallToUseStatementRector

Change `App::uses()` to use imports

- class: [`Rector\CakePHP\Rector\Namespace_\AppUsesStaticCallToUseStatementRector`](../src/Rector/Namespace_/AppUsesStaticCallToUseStatementRector.php)

```diff
-App::uses('NotificationListener', 'Event');
+use Event\NotificationListener;

 CakeEventManager::instance()->attach(new NotificationListener());
```

<br>

## ArrayToFluentCallRector

Moves array options to fluent setter method calls.

:wrench: **configure it!**

- class: [`Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector`](../src/Rector/MethodCall/ArrayToFluentCallRector.php)

```php
use Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector;
use Rector\CakePHP\ValueObject\ArrayToFluentCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayToFluentCallRector::class)
        ->configure([
ArrayToFluentCallRector::ARRAYS_TO_FLUENT_CALLS => ValueObjectInliner::inline([
    new ArrayToFluentCall('ArticlesTable', ['setForeignKey', 'setProperty']), ]
),
]]);
};
```

↓

```diff
 use Cake\ORM\Table;

 final class ArticlesTable extends Table
 {
     public function initialize(array $config)
     {
-        $this->belongsTo('Authors', [
-            'foreignKey' => 'author_id',
-            'propertyName' => 'person'
-        ]);
+        $this->belongsTo('Authors')
+            ->setForeignKey('author_id')
+            ->setProperty('person');
     }
 }
```

<br>

## ChangeSnakedFixtureNameToPascalRector

Changes `$fixtures` style from snake_case to PascalCase.

- class: [`Rector\CakePHP\Rector\Property\ChangeSnakedFixtureNameToPascalRector`](../src/Rector/Property/ChangeSnakedFixtureNameToPascalRector.php)

```diff
 class SomeTest
 {
     protected $fixtures = [
-        'app.posts',
-        'app.users',
-        'some_plugin.posts/special_posts',
+        'app.Posts',
+        'app.Users',
+        'some_plugin.Posts/SpecialPosts',
     ];
```

<br>

## ImplicitShortClassNameUseStatementRector

Collect implicit class names and add imports

- class: [`Rector\CakePHP\Rector\FileWithoutNamespace\ImplicitShortClassNameUseStatementRector`](../src/Rector/FileWithoutNamespace/ImplicitShortClassNameUseStatementRector.php)

```diff
 use App\Foo\Plugin;
+use Cake\TestSuite\Fixture\TestFixture;

 class LocationsFixture extends TestFixture implements Plugin
 {
 }
```

<br>

## ModalToGetSetRector

Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.

:wrench: **configure it!**

- class: [`Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector`](../src/Rector/MethodCall/ModalToGetSetRector.php)

```php
use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ModalToGetSetRector::class)
        ->configure([
ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => ValueObjectInliner::inline([
    new ModalToGetSet('InstanceConfigTrait', 'config', 'getConfig', 'setConfig', 1, null),
    ]),
]]);
};
```

↓

```diff
 $object = new InstanceConfigTrait;

-$config = $object->config();
-$config = $object->config('key');
+$config = $object->getConfig();
+$config = $object->getConfig('key');

-$object->config('key', 'value');
-$object->config(['key' => 'value']);
+$object->setConfig('key', 'value');
+$object->setConfig(['key' => 'value']);
```

<br>

## RenameMethodCallBasedOnParameterRector

Changes method calls based on matching the first parameter value.

:wrench: **configure it!**

- class: [`Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector`](../src/Rector/MethodCall/RenameMethodCallBasedOnParameterRector.php)

```php
use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodCallBasedOnParameterRector::class)
        ->configure([
RenameMethodCallBasedOnParameterRector::CALLS_WITH_PARAM_RENAMES => ValueObjectInliner::inline([
    new RenameMethodCallBasedOnParameter('getParam', 'paging', 'getAttribute', 'ServerRequest'),
    new RenameMethodCallBasedOnParameter('withParam', 'paging', 'withAttribute', 'ServerRequest'),
    ]),
]]);
};
```

↓

```diff
 $object = new ServerRequest();

-$config = $object->getParam('paging');
-$object = $object->withParam('paging', ['a value']);
+$config = $object->getAttribute('paging');
+$object = $object->withAttribute('paging', ['a value']);
```

<br>
