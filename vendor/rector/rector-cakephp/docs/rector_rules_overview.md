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
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ArrayToFluentCallRector::class, [Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector::ARRAYS_TO_FLUENT_CALLS: [new ArrayToFluentCall('ArticlesTable', ['setForeignKey', 'setProperty'])]]);
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

## ModalToGetSetRector

Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.

:wrench: **configure it!**

- class: [`Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector`](../src/Rector/MethodCall/ModalToGetSetRector.php)

```php
use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ModalToGetSetRector::class, [Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET: [new ModalToGetSet('getConfig', 'setConfig', 'InstanceConfigTrait', 'config', 1)]]);
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

## RemoveIntermediaryMethodRector

Removes an intermediary method call for when a higher level API is added.

:wrench: **configure it!**

- class: [`Rector\CakePHP\Rector\MethodCall\RemoveIntermediaryMethodRector`](../src/Rector/MethodCall/RemoveIntermediaryMethodRector.php)

```php
use Rector\CakePHP\Rector\MethodCall\RemoveIntermediaryMethodRector;
use Rector\CakePHP\ValueObject\RemoveIntermediaryMethod;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RemoveIntermediaryMethodRector::class, [Rector\CakePHP\Rector\MethodCall\RemoveIntermediaryMethodRector::REMOVE_INTERMEDIARY_METHOD: [new RemoveIntermediaryMethod('getTableLocator', 'get', 'fetchTable')]]);
};
```

↓

```diff
-$users = $this->getTableLocator()->get('Users');
+$users = $this->fetchTable('Users');
```

<br>

## RenameMethodCallBasedOnParameterRector

Changes method calls based on matching the first parameter value.

:wrench: **configure it!**

- class: [`Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector`](../src/Rector/MethodCall/RenameMethodCallBasedOnParameterRector.php)

```php
use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodCallBasedOnParameterRector::class, [Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector::CALLS_WITH_PARAM_RENAMES: [new RenameMethodCallBasedOnParameter('ServerRequest', 'getParam', 'paging', 'getAttribute'), new RenameMethodCallBasedOnParameter('ServerRequest', 'withParam', 'paging', 'withAttribute')]]);
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
