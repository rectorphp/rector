# All 582 Rectors Overview

- [Projects](#projects)
---

## Projects

- [Architecture](#architecture) (2)
- [Autodiscovery](#autodiscovery) (4)
- [CakePHP](#cakephp) (6)
- [CodeQuality](#codequality) (59)
- [CodingStyle](#codingstyle) (34)
- [DeadCode](#deadcode) (40)
- [Decouple](#decouple) (1)
- [Doctrine](#doctrine) (17)
- [DoctrineCodeQuality](#doctrinecodequality) (8)
- [DoctrineGedmoToKnplabs](#doctrinegedmotoknplabs) (7)
- [DowngradePhp71](#downgradephp71) (3)
- [DowngradePhp72](#downgradephp72) (2)
- [DowngradePhp74](#downgradephp74) (3)
- [DowngradePhp80](#downgradephp80) (6)
- [DynamicTypeAnalysis](#dynamictypeanalysis) (3)
- [FileSystemRector](#filesystemrector) (1)
- [Generic](#generic) (37)
- [JMS](#jms) (2)
- [Laravel](#laravel) (3)
- [Legacy](#legacy) (4)
- [MagicDisclosure](#magicdisclosure) (8)
- [MockeryToProphecy](#mockerytoprophecy) (2)
- [MockistaToMockery](#mockistatomockery) (2)
- [MysqlToMysqli](#mysqltomysqli) (4)
- [Naming](#naming) (10)
- [Nette](#nette) (16)
- [NetteCodeQuality](#nettecodequality) (6)
- [NetteKdyby](#nettekdyby) (4)
- [NetteTesterToPHPUnit](#nettetestertophpunit) (3)
- [NetteToSymfony](#nettetosymfony) (9)
- [NetteUtilsCodeQuality](#netteutilscodequality) (1)
- [Order](#order) (9)
- [PHPOffice](#phpoffice) (14)
- [PHPStan](#phpstan) (3)
- [PHPUnit](#phpunit) (38)
- [PHPUnitSymfony](#phpunitsymfony) (1)
- [PSR4](#psr4) (3)
- [Performance](#performance) (1)
- [Phalcon](#phalcon) (4)
- [Php52](#php52) (2)
- [Php53](#php53) (4)
- [Php54](#php54) (2)
- [Php55](#php55) (2)
- [Php56](#php56) (2)
- [Php70](#php70) (19)
- [Php71](#php71) (9)
- [Php72](#php72) (11)
- [Php73](#php73) (10)
- [Php74](#php74) (15)
- [Php80](#php80) (12)
- [PhpDeglobalize](#phpdeglobalize) (1)
- [PhpSpecToPHPUnit](#phpspectophpunit) (7)
- [Polyfill](#polyfill) (2)
- [Privatization](#privatization) (7)
- [RectorGenerator](#rectorgenerator) (1)
- [RemovingStatic](#removingstatic) (6)
- [Renaming](#renaming) (9)
- [Restoration](#restoration) (7)
- [SOLID](#solid) (12)
- [Sensio](#sensio) (3)
- [StrictCodeQuality](#strictcodequality) (1)
- [Symfony](#symfony) (33)
- [SymfonyCodeQuality](#symfonycodequality) (1)
- [SymfonyPHPUnit](#symfonyphpunit) (1)
- [SymfonyPhpConfig](#symfonyphpconfig) (2)
- [Transform](#transform) (11)
- [Twig](#twig) (1)
- [TypeDeclaration](#typedeclaration) (9)

## Architecture

### `ReplaceParentRepositoryCallsByRepositoryPropertyRector`

- class: [`Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector`](/rules/architecture/src/Rector/MethodCall/ReplaceParentRepositoryCallsByRepositoryPropertyRector.php)

Handles method calls in child of Doctrine EntityRepository and moves them to `$this->repository` property.

```diff
 <?php

 use Doctrine\ORM\EntityRepository;

 class SomeRepository extends EntityRepository
 {
     public function someMethod()
     {
-        return $this->findAll();
+        return $this->repository->findAll();
     }
 }
```

<br><br>

### `ServiceLocatorToDIRector`

- class: [`Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector`](/rules/architecture/src/Rector/MethodCall/ServiceLocatorToDIRector.php)

Turns `$this->getRepository()` in Symfony Controller to constructor injection and private property access.

```diff
 class ProductController extends Controller
 {
+    /**
+     * @var ProductRepository
+     */
+    private $productRepository;
+
+    public function __construct(ProductRepository $productRepository)
+    {
+        $this->productRepository = $productRepository;
+    }
+
     public function someAction()
     {
         $entityManager = $this->getDoctrine()->getManager();
-        $entityManager->getRepository('SomethingBundle:Product')->findSomething(...);
+        $this->productRepository->findSomething(...);
     }
 }
```

<br><br>

## Autodiscovery

### `MoveEntitiesToEntityDirectoryRector`

- class: [`Rector\Autodiscovery\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector`](/rules/autodiscovery/src/Rector/FileSystem/MoveEntitiesToEntityDirectoryRector.php)

Move entities to Entity namespace

```diff
-// file: app/Controller/Product.php
+// file: app/Entity/Product.php

-namespace App\Controller;
+namespace App\Entity;

 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class Product
 {
 }
```

<br><br>

### `MoveInterfacesToContractNamespaceDirectoryRector`

- class: [`Rector\Autodiscovery\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector`](/rules/autodiscovery/src/Rector/FileSystem/MoveInterfacesToContractNamespaceDirectoryRector.php)

Move interface to "Contract" namespace

```diff
-// file: app/Exception/Rule.php
+// file: app/Contract/Rule.php

-namespace App\Exception;
+namespace App\Contract;

 interface Rule
 {
 }
```

<br><br>

### `MoveServicesBySuffixToDirectoryRector`

- class: [`Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector`](/rules/autodiscovery/src/Rector/FileSystem/MoveServicesBySuffixToDirectoryRector.php)

Move classes by their suffix to their own group/directory

```php
<?php

declare(strict_types=1);

use Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MoveServicesBySuffixToDirectoryRector::class)
        ->call('configure', [[
            MoveServicesBySuffixToDirectoryRector::GROUP_NAMES_BY_SUFFIX => ['Repository'],
        ]]);
};
```

↓

```diff
-// file: app/Entity/ProductRepository.php
+// file: app/Repository/ProductRepository.php

-namespace App/Entity;
+namespace App/Repository;

 class ProductRepository
 {
 }
```

<br><br>

### `MoveValueObjectsToValueObjectDirectoryRector`

- class: [`Rector\Autodiscovery\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector`](/rules/autodiscovery/src/Rector/FileSystem/MoveValueObjectsToValueObjectDirectoryRector.php)

Move value object to ValueObject namespace/directory

```php
<?php

declare(strict_types=1);

use Rector\Autodiscovery\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MoveValueObjectsToValueObjectDirectoryRector::class)
        ->call(
            'configure',
            [[
                MoveValueObjectsToValueObjectDirectoryRector::TYPES => [
                    'ValueObjectInterfaceClassName',
                ],
                MoveValueObjectsToValueObjectDirectoryRector::SUFFIXES => [
                    'Search',
                ],
                MoveValueObjectsToValueObjectDirectoryRector::ENABLE_VALUE_OBJECT_GUESSING => true,
            ]]
        );
};
```

↓

```diff
-// app/Exception/Name.php
+// app/ValueObject/Name.php
 class Name
 {
     private $name;

     public function __construct(string $name)
     {
         $this->name = $name;
     }

     public function getName()
     {
         return $this->name;
     }
 }
```

<br><br>

## CakePHP

### `AppUsesStaticCallToUseStatementRector`

- class: [`Rector\CakePHP\Rector\Expression\AppUsesStaticCallToUseStatementRector`](/rules/cakephp/src/Rector/Expression/AppUsesStaticCallToUseStatementRector.php)
- [test fixtures](/rules/cakephp/tests/Rector/Expression/AppUsesStaticCallToUseStatementRector/Fixture)

Change `App::uses()` to use imports

```diff
-App::uses('NotificationListener', 'Event');
+use Event\NotificationListener;

 CakeEventManager::instance()->attach(new NotificationListener());
```

<br><br>

### `ArrayToFluentCallRector`

- class: [`Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector`](/rules/cakephp/src/Rector/MethodCall/ArrayToFluentCallRector.php)
- [test fixtures](/rules/cakephp/tests/Rector/MethodCall/ArrayToFluentCallRector/Fixture)

Moves array options to fluent setter method calls.

```php
<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector;
use Rector\CakePHP\ValueObject\ArrayToFluentCall;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayToFluentCallRector::class)
        ->call(
            'configure',
            [[
                ArrayToFluentCallRector::ARRAYS_TO_FLUENT_CALLS => inline_value_objects(
                                [new ArrayToFluentCall('ArticlesTable', [
                                    'foreignKey' => 'setForeignKey',
                                    'propertyName' => 'setProperty',
                                ])]
                            )
            ]]
        );
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

<br><br>

### `ChangeSnakedFixtureNameToPascalRector`

- class: [`Rector\CakePHP\Rector\Property\ChangeSnakedFixtureNameToPascalRector`](/rules/cakephp/src/Rector/Property/ChangeSnakedFixtureNameToPascalRector.php)

Changes `$fixtues` style from snake_case to PascalCase.

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

<br><br>

### `ImplicitShortClassNameUseStatementRector`

- class: [`Rector\CakePHP\Rector\Name\ImplicitShortClassNameUseStatementRector`](/rules/cakephp/src/Rector/Name/ImplicitShortClassNameUseStatementRector.php)
- [test fixtures](/rules/cakephp/tests/Rector/Name/ImplicitShortClassNameUseStatementRector/Fixture)

Collect implicit class names and add imports

```diff
 use App\Foo\Plugin;
+use Cake\TestSuite\Fixture\TestFixture;

 class LocationsFixture extends TestFixture implements Plugin
 {
 }
```

<br><br>

### `ModalToGetSetRector`

- class: [`Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector`](/rules/cakephp/src/Rector/MethodCall/ModalToGetSetRector.php)
- [test fixtures](/rules/cakephp/tests/Rector/MethodCall/ModalToGetSetRector/Fixture)

Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.

```php
<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ModalToGetSetRector::class)
        ->call(
            'configure',
            [[
                ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => inline_value_objects(
                                [new ModalToGetSet('InstanceConfigTrait', 'config', 'getConfig', 'setConfig', 1, null)]
                            )
            ]]
        );
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

<br><br>

### `RenameMethodCallBasedOnParameterRector`

- class: [`Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector`](/rules/cakephp/src/Rector/MethodCall/RenameMethodCallBasedOnParameterRector.php)
- [test fixtures](/rules/cakephp/tests/Rector/MethodCall/RenameMethodCallBasedOnParameterRector/Fixture)

Changes method calls based on matching the first parameter value.

```php
<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodCallBasedOnParameterRector::class)
        ->call(
            'configure',
            [[
                RenameMethodCallBasedOnParameterRector::CALLS_WITH_PARAM_RENAMES => inline_value_objects(
                                [new RenameMethodCallBasedOnParameter(
                                    'getParam',
                                    'paging',
                                    'getAttribute',
                                    'ServerRequest'
                                ), new RenameMethodCallBasedOnParameter(
                                    'withParam',
                                    'paging',
                                    'withAttribute',
                                    'ServerRequest'
                                )]
                            )
            ]]
        );
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

<br><br>

## CodeQuality

### `AbsolutizeRequireAndIncludePathRector`

- class: [`Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector`](/rules/code-quality/src/Rector/Include_/AbsolutizeRequireAndIncludePathRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Include_/AbsolutizeRequireAndIncludePathRector/Fixture)

include/require to absolute path. This Rector might introduce backwards incompatible code, when the include/require beeing changed depends on the current working directory.

```diff
 class SomeClass
 {
     public function run()
     {
-        require 'autoload.php';
+        require __DIR__ . '/autoload.php';

         require $variable;
     }
 }
```

<br><br>

### `AddPregQuoteDelimiterRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector`](/rules/code-quality/src/Rector/FuncCall/AddPregQuoteDelimiterRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/AddPregQuoteDelimiterRector/Fixture)

Add `preg_quote` delimiter when missing

```diff
-'#' . preg_quote('name') . '#';
+'#' . preg_quote('name', '#') . '#';
```

<br><br>

### `AndAssignsToSeparateLinesRector`

- class: [`Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector`](/rules/code-quality/src/Rector/LogicalAnd/AndAssignsToSeparateLinesRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/LogicalAnd/AndAssignsToSeparateLinesRector/Fixture)

Split 2 assigns ands to separate line

```diff
 class SomeClass
 {
     public function run()
     {
         $tokens = [];
-        $token = 4 and $tokens[] = $token;
+        $token = 4;
+        $tokens[] = $token;
     }
 }
```

<br><br>

### `ArrayKeyExistsTernaryThenValueToCoalescingRector`

- class: [`Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector`](/rules/code-quality/src/Rector/Ternary/ArrayKeyExistsTernaryThenValueToCoalescingRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Ternary/ArrayKeyExistsTernaryThenValueToCoalescingRector/Fixture)

Change `array_key_exists()` ternary to coalesing

```diff
 class SomeClass
 {
     public function run($values, $keyToMatch)
     {
-        $result = array_key_exists($keyToMatch, $values) ? $values[$keyToMatch] : null;
+        $result = $values[$keyToMatch] ?? null;
     }
 }
```

<br><br>

### `ArrayKeysAndInArrayToArrayKeyExistsRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector`](/rules/code-quality/src/Rector/FuncCall/ArrayKeysAndInArrayToArrayKeyExistsRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/ArrayKeysAndInArrayToArrayKeyExistsRector/Fixture)

Replace `array_keys()` and `in_array()` to `array_key_exists()`

```diff
 class SomeClass
 {
     public function run($packageName, $values)
     {
-        $keys = array_keys($values);
-        return in_array($packageName, $keys, true);
+        return array_key_exists($packageName, $values);
     }
 }
```

<br><br>

### `ArrayMergeOfNonArraysToSimpleArrayRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector`](/rules/code-quality/src/Rector/FuncCall/ArrayMergeOfNonArraysToSimpleArrayRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/ArrayMergeOfNonArraysToSimpleArrayRector/Fixture)

Change `array_merge` of non arrays to array directly

```diff
 class SomeClass
 {
     public function go()
     {
         $value = 5;
         $value2 = 10;

-        return array_merge([$value], [$value2]);
+        return [$value, $value2];
     }
 }
```

<br><br>

### `ArrayThisCallToThisMethodCallRector`

- class: [`Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector`](/rules/code-quality/src/Rector/Array_/ArrayThisCallToThisMethodCallRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Array_/ArrayThisCallToThisMethodCallRector/Fixture)

Change `[$this, someMethod]` without any args to `$this->someMethod()`

```diff
 class SomeClass
 {
     public function run()
     {
-        $values = [$this, 'giveMeMore'];
+        $values = $this->giveMeMore();
     }

     public function giveMeMore()
     {
         return 'more';
     }
 }
```

<br><br>

### `BooleanNotIdenticalToNotIdenticalRector`

- class: [`Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector`](/rules/code-quality/src/Rector/Identical/BooleanNotIdenticalToNotIdenticalRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Identical/BooleanNotIdenticalToNotIdenticalRector/Fixture)

Negated identical boolean compare to not identical compare (does not apply to non-bool values)

```diff
 class SomeClass
 {
     public function run()
     {
         $a = true;
         $b = false;

-        var_dump(! $a === $b); // true
-        var_dump(! ($a === $b)); // true
+        var_dump($a !== $b); // true
+        var_dump($a !== $b); // true
         var_dump($a !== $b); // true
     }
 }
```

<br><br>

### `CallableThisArrayToAnonymousFunctionRector`

- class: [`Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector`](/rules/code-quality/src/Rector/Array_/CallableThisArrayToAnonymousFunctionRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Array_/CallableThisArrayToAnonymousFunctionRector/Fixture)

Convert [$this, "method"] to proper anonymous function

```diff
 class SomeClass
 {
     public function run()
     {
         $values = [1, 5, 3];
-        usort($values, [$this, 'compareSize']);
+        usort($values, function ($first, $second) {
+            return $this->compareSize($first, $second);
+        });

         return $values;
     }

     private function compareSize($first, $second)
     {
         return $first <=> $second;
     }
 }
```

<br><br>

### `ChangeArrayPushToArrayAssignRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector`](/rules/code-quality/src/Rector/FuncCall/ChangeArrayPushToArrayAssignRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/ChangeArrayPushToArrayAssignRector/Fixture)

Change `array_push()` to direct variable assign

```diff
 class SomeClass
 {
     public function run()
     {
         $items = [];
-        array_push($items, $item);
+        $items[] = $item;
     }
 }
```

<br><br>

### `CombineIfRector`

- class: [`Rector\CodeQuality\Rector\If_\CombineIfRector`](/rules/code-quality/src/Rector/If_/CombineIfRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/If_/CombineIfRector/Fixture)

Merges nested if statements

```diff
 class SomeClass
 {
     public function run()
     {
-        if ($cond1) {
-            if ($cond2) {
-                return 'foo';
-            }
+        if ($cond1 && $cond2) {
+            return 'foo';
         }
     }
 }
```

<br><br>

### `CombinedAssignRector`

- class: [`Rector\CodeQuality\Rector\Assign\CombinedAssignRector`](/rules/code-quality/src/Rector/Assign/CombinedAssignRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Assign/CombinedAssignRector/Fixture)

Simplify `$value` = `$value` + 5; assignments to shorter ones

```diff
-$value = $value + 5;
+$value += 5;
```

<br><br>

### `CommonNotEqualRector`

- class: [`Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector`](/rules/code-quality/src/Rector/NotEqual/CommonNotEqualRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/NotEqual/CommonNotEqualRector/Fixture)

Use common != instead of less known <> with same meaning

```diff
 final class SomeClass
 {
     public function run($one, $two)
     {
-        return $one <> $two;
+        return $one != $two;
     }
 }
```

<br><br>

### `CompactToVariablesRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector`](/rules/code-quality/src/Rector/FuncCall/CompactToVariablesRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/CompactToVariablesRector/Fixture)

Change `compact()` call to own array

```diff
 class SomeClass
 {
     public function run()
     {
         $checkout = 'one';
         $form = 'two';

-        return compact('checkout', 'form');
+        return ['checkout' => $checkout, 'form' => $form];
     }
 }
```

<br><br>

### `CompleteDynamicPropertiesRector`

- class: [`Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector`](/rules/code-quality/src/Rector/Class_/CompleteDynamicPropertiesRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Class_/CompleteDynamicPropertiesRector/Fixture)

Add missing dynamic properties

```diff
 class SomeClass
 {
+    /**
+     * @var int
+     */
+    public $value;
     public function set()
     {
         $this->value = 5;
     }
 }
```

<br><br>

### `ConsecutiveNullCompareReturnsToNullCoalesceQueueRector`

- class: [`Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector`](/rules/code-quality/src/Rector/If_/ConsecutiveNullCompareReturnsToNullCoalesceQueueRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/If_/ConsecutiveNullCompareReturnsToNullCoalesceQueueRector/Fixture)

Change multiple null compares to ?? queue

```diff
 class SomeClass
 {
     public function run()
     {
-        if (null !== $this->orderItem) {
-            return $this->orderItem;
-        }
-
-        if (null !== $this->orderItemUnit) {
-            return $this->orderItemUnit;
-        }
-
-        return null;
+        return $this->orderItem ?? $this->orderItemUnit;
     }
 }
```

<br><br>

### `ExplicitBoolCompareRector`

- class: [`Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector`](/rules/code-quality/src/Rector/If_/ExplicitBoolCompareRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/If_/ExplicitBoolCompareRector/Fixture)

Make if conditions more explicit

```diff
 final class SomeController
 {
     public function run($items)
     {
-        if (!count($items)) {
+        if (count($items) === 0) {
             return 'no items';
         }
     }
 }
```

<br><br>

### `FixClassCaseSensitivityNameRector`

- class: [`Rector\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector`](/rules/code-quality/src/Rector/Name/FixClassCaseSensitivityNameRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Name/FixClassCaseSensitivityNameRector/Fixture)

Change miss-typed case sensitivity name to correct one

```diff
 final class SomeClass
 {
     public function run()
     {
-        $anotherClass = new anotherclass;
+        $anotherClass = new AnotherClass;
     }
 }

 final class AnotherClass
 {
 }
```

<br><br>

### `ForRepeatedCountToOwnVariableRector`

- class: [`Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector`](/rules/code-quality/src/Rector/For_/ForRepeatedCountToOwnVariableRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/For_/ForRepeatedCountToOwnVariableRector/Fixture)

Change `count()` in for function to own variable

```diff
 class SomeClass
 {
     public function run($items)
     {
-        for ($i = 5; $i <= count($items); $i++) {
+        $itemsCount = count($items);
+        for ($i = 5; $i <= $itemsCount; $i++) {
             echo $items[$i];
         }
     }
 }
```

<br><br>

### `ForToForeachRector`

- class: [`Rector\CodeQuality\Rector\For_\ForToForeachRector`](/rules/code-quality/src/Rector/For_/ForToForeachRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/For_/ForToForeachRector/Fixture)

Change `for()` to `foreach()` where useful

```diff
 class SomeClass
 {
     public function run($tokens)
     {
-        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
-            if ($tokens[$i][0] === T_STRING && $tokens[$i][1] === 'fn') {
+        foreach ($tokens as $i => $token) {
+            if ($token[0] === T_STRING && $token[1] === 'fn') {
                 $previousNonSpaceToken = $this->getPreviousNonSpaceToken($tokens, $i);
                 if ($previousNonSpaceToken !== null && $previousNonSpaceToken[0] === T_OBJECT_OPERATOR) {
                     continue;
                 }
                 $tokens[$i][0] = self::T_FN;
             }
         }
     }
 }
```

<br><br>

### `ForeachItemsAssignToEmptyArrayToAssignRector`

- class: [`Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector`](/rules/code-quality/src/Rector/Foreach_/ForeachItemsAssignToEmptyArrayToAssignRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Foreach_/ForeachItemsAssignToEmptyArrayToAssignRector/Fixture)

Change `foreach()` items assign to empty array to direct assign

```diff
 class SomeClass
 {
     public function run($items)
     {
         $items2 = [];
-        foreach ($items as $item) {
-             $items2[] = $item;
-        }
+        $items2 = $items;
     }
 }
```

<br><br>

### `ForeachToInArrayRector`

- class: [`Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector`](/rules/code-quality/src/Rector/Foreach_/ForeachToInArrayRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Foreach_/ForeachToInArrayRector/Fixture)

Simplify `foreach` loops into `in_array` when possible

```diff
-foreach ($items as $item) {
-    if ($item === 'something') {
-        return true;
-    }
-}
-
-return false;
+in_array("something", $items, true);
```

<br><br>

### `GetClassToInstanceOfRector`

- class: [`Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector`](/rules/code-quality/src/Rector/Identical/GetClassToInstanceOfRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Identical/GetClassToInstanceOfRector/Fixture)

Changes comparison with `get_class` to instanceof

```diff
-if (EventsListener::class === get_class($event->job)) { }
+if ($event->job instanceof EventsListener) { }
```

<br><br>

### `InArrayAndArrayKeysToArrayKeyExistsRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector`](/rules/code-quality/src/Rector/FuncCall/InArrayAndArrayKeysToArrayKeyExistsRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/InArrayAndArrayKeysToArrayKeyExistsRector/Fixture)

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
```

<br><br>

### `InlineIfToExplicitIfRector`

- class: [`Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector`](/rules/code-quality/src/Rector/Expression/InlineIfToExplicitIfRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Expression/InlineIfToExplicitIfRector/Fixture)

Change inline if to explicit if

```diff
 class SomeClass
 {
     public function run()
     {
         $userId = null;

-        is_null($userId) && $userId = 5;
+        if (is_null($userId)) {
+            $userId = 5;
+        }
     }
 }
```

<br><br>

### `IntvalToTypeCastRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector`](/rules/code-quality/src/Rector/FuncCall/IntvalToTypeCastRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/IntvalToTypeCastRector/Fixture)

Change `intval()` to faster and readable (int) `$value`

```diff
 class SomeClass
 {
     public function run($value)
     {
-        return intval($value);
+        return (int) $value;
     }
 }
```

<br><br>

### `IsAWithStringWithThirdArgumentRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector`](/rules/code-quality/src/Rector/FuncCall/IsAWithStringWithThirdArgumentRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/IsAWithStringWithThirdArgumentRector/Fixture)

Complete missing 3rd argument in case `is_a()` function in case of strings

```diff
 class SomeClass
 {
     public function __construct(string $value)
     {
-        return is_a($value, 'stdClass');
+        return is_a($value, 'stdClass', true);
     }
 }
```

<br><br>

### `IssetOnPropertyObjectToPropertyExistsRector`

- class: [`Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector`](/rules/code-quality/src/Rector/Isset_/IssetOnPropertyObjectToPropertyExistsRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Isset_/IssetOnPropertyObjectToPropertyExistsRector/Fixture)

Change isset on property object to `property_exists()`

```diff
 class SomeClass
 {
     private $x;

     public function run(): void
     {
-        isset($this->x);
+        property_exists($this, 'x') && $this->x !== null;
     }
 }
```

<br><br>

### `JoinStringConcatRector`

- class: [`Rector\CodeQuality\Rector\Concat\JoinStringConcatRector`](/rules/code-quality/src/Rector/Concat/JoinStringConcatRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Concat/JoinStringConcatRector/Fixture)

Joins concat of 2 strings, unless the lenght is too long

```diff
 class SomeClass
 {
     public function run()
     {
-        $name = 'Hi' . ' Tom';
+        $name = 'Hi Tom';
     }
 }
```

<br><br>

### `LogicalToBooleanRector`

- class: [`Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector`](/rules/code-quality/src/Rector/LogicalAnd/LogicalToBooleanRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/LogicalAnd/LogicalToBooleanRector/Fixture)

Change OR, AND to ||, && with more common understanding

```diff
-if ($f = false or true) {
+if (($f = false) || true) {
     return $f;
 }
```

<br><br>

### `RemoveAlwaysTrueConditionSetInConstructorRector`

- class: [`Rector\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector`](/rules/code-quality/src/Rector/FunctionLike/RemoveAlwaysTrueConditionSetInConstructorRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FunctionLike/RemoveAlwaysTrueConditionSetInConstructorRector/Fixture)

If conditions is always true, perform the content right away

```diff
 final class SomeClass
 {
     private $value;

     public function __construct($value)
     {
         $this->value = $value;
     }

     public function go()
     {
-        if ($this->value) {
-            return 'yes';
-        }
+        return 'yes';
     }
 }
```

<br><br>

### `RemoveSoleValueSprintfRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector`](/rules/code-quality/src/Rector/FuncCall/RemoveSoleValueSprintfRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/RemoveSoleValueSprintfRector/Fixture)

Remove `sprintf()` wrapper if not needed

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = sprintf('%s', 'hi');
+        $value = 'hi';

         $welcome = 'hello';
-        $value = sprintf('%s', $welcome);
+        $value = $welcome;
     }
 }
```

<br><br>

### `SetTypeToCastRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector`](/rules/code-quality/src/Rector/FuncCall/SetTypeToCastRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/SetTypeToCastRector/Fixture)

Changes `settype()` to (type) where possible

```diff
 class SomeClass
 {
-    public function run($foo)
+    public function run(array $items)
     {
-        settype($foo, 'string');
+        $foo = (string) $foo;

-        return settype($foo, 'integer');
+        return (int) $foo;
     }
 }
```

<br><br>

### `ShortenElseIfRector`

- class: [`Rector\CodeQuality\Rector\If_\ShortenElseIfRector`](/rules/code-quality/src/Rector/If_/ShortenElseIfRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/If_/ShortenElseIfRector/Fixture)

Shortens else/if to elseif

```diff
 class SomeClass
 {
     public function run()
     {
         if ($cond1) {
             return $action1;
-        } else {
-            if ($cond2) {
-                return $action2;
-            }
+        } elseif ($cond2) {
+            return $action2;
         }
     }
 }
```

<br><br>

### `SimplifyArraySearchRector`

- class: [`Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector`](/rules/code-quality/src/Rector/Identical/SimplifyArraySearchRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Identical/SimplifyArraySearchRector/Fixture)

Simplify `array_search` to `in_array`

```diff
-array_search("searching", $array) !== false;
+in_array("searching", $array);
```
```diff
-array_search("searching", $array, true) !== false;
+in_array("searching", $array, true);
```

<br><br>

### `SimplifyBoolIdenticalTrueRector`

- class: [`Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector`](/rules/code-quality/src/Rector/Identical/SimplifyBoolIdenticalTrueRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Identical/SimplifyBoolIdenticalTrueRector/Fixture)

Symplify bool value compare to true or false

```diff
 class SomeClass
 {
     public function run(bool $value, string $items)
     {
-         $match = in_array($value, $items, TRUE) === TRUE;
-         $match = in_array($value, $items, TRUE) !== FALSE;
+         $match = in_array($value, $items, TRUE);
+         $match = in_array($value, $items, TRUE);
     }
 }
```

<br><br>

### `SimplifyConditionsRector`

- class: [`Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector`](/rules/code-quality/src/Rector/Identical/SimplifyConditionsRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Identical/SimplifyConditionsRector/Fixture)

Simplify conditions

```diff
-if (! ($foo !== 'bar')) {...
+if ($foo === 'bar') {...
```

<br><br>

### `SimplifyDeMorganBinaryRector`

- class: [`Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector`](/rules/code-quality/src/Rector/BooleanNot/SimplifyDeMorganBinaryRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/BooleanNot/SimplifyDeMorganBinaryRector/Fixture)

Simplify negated conditions with de Morgan theorem

```diff
 <?php

 $a = 5;
 $b = 10;
-$result = !($a > 20 || $b <= 50);
+$result = $a <= 20 && $b > 50;
```

<br><br>

### `SimplifyDuplicatedTernaryRector`

- class: [`Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector`](/rules/code-quality/src/Rector/Ternary/SimplifyDuplicatedTernaryRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Ternary/SimplifyDuplicatedTernaryRector/Fixture)

Remove ternary that duplicated return value of true : false

```diff
 class SomeClass
 {
     public function run(bool $value, string $name)
     {
-         $isTrue = $value ? true : false;
+         $isTrue = $value;
          $isName = $name ? true : false;
     }
 }
```

<br><br>

### `SimplifyEmptyArrayCheckRector`

- class: [`Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector`](/rules/code-quality/src/Rector/BooleanAnd/SimplifyEmptyArrayCheckRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/BooleanAnd/SimplifyEmptyArrayCheckRector/Fixture)

Simplify `is_array` and `empty` functions combination into a simple identical check for an empty array

```diff
-is_array($values) && empty($values)
+$values === []
```

<br><br>

### `SimplifyForeachToArrayFilterRector`

- class: [`Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector`](/rules/code-quality/src/Rector/Foreach_/SimplifyForeachToArrayFilterRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Foreach_/SimplifyForeachToArrayFilterRector/Fixture)

Simplify foreach with function filtering to array filter

```diff
-$directories = [];
 $possibleDirectories = [];
-foreach ($possibleDirectories as $possibleDirectory) {
-    if (file_exists($possibleDirectory)) {
-        $directories[] = $possibleDirectory;
-    }
-}
+$directories = array_filter($possibleDirectories, 'file_exists');
```

<br><br>

### `SimplifyForeachToCoalescingRector`

- class: [`Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector`](/rules/code-quality/src/Rector/Foreach_/SimplifyForeachToCoalescingRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Foreach_/SimplifyForeachToCoalescingRector/Fixture)

Changes foreach that returns set value to ??

```diff
-foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
-    if ($currentFunction === $oldFunction) {
-        return $newFunction;
-    }
-}
-
-return null;
+return $this->oldToNewFunctions[$currentFunction] ?? null;
```

<br><br>

### `SimplifyFuncGetArgsCountRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector`](/rules/code-quality/src/Rector/FuncCall/SimplifyFuncGetArgsCountRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/SimplifyFuncGetArgsCountRector/Fixture)

Simplify `count` of `func_get_args()` to `func_num_args()`

```diff
-count(func_get_args());
+func_num_args();
```

<br><br>

### `SimplifyIfElseToTernaryRector`

- class: [`Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector`](/rules/code-quality/src/Rector/If_/SimplifyIfElseToTernaryRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/If_/SimplifyIfElseToTernaryRector/Fixture)

Changes if/else for same value as assign to ternary

```diff
 class SomeClass
 {
     public function run()
     {
-        if (empty($value)) {
-            $this->arrayBuilt[][$key] = true;
-        } else {
-            $this->arrayBuilt[][$key] = $value;
-        }
+        $this->arrayBuilt[][$key] = empty($value) ? true : $value;
     }
 }
```

<br><br>

### `SimplifyIfIssetToNullCoalescingRector`

- class: [`Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector`](/rules/code-quality/src/Rector/If_/SimplifyIfIssetToNullCoalescingRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/If_/SimplifyIfIssetToNullCoalescingRector/Fixture)

Simplify binary if to null coalesce

```diff
 final class SomeController
 {
     public function run($possibleStatieYamlFile)
     {
-        if (isset($possibleStatieYamlFile['import'])) {
-            $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'], $filesToImport);
-        } else {
-            $possibleStatieYamlFile['import'] = $filesToImport;
-        }
+        $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'] ?? [], $filesToImport);
     }
 }
```

<br><br>

### `SimplifyIfNotNullReturnRector`

- class: [`Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector`](/rules/code-quality/src/Rector/If_/SimplifyIfNotNullReturnRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/If_/SimplifyIfNotNullReturnRector/Fixture)

Changes redundant null check to instant return

```diff
 $newNode = 'something ;
-if ($newNode !== null) {
-    return $newNode;
-}
-
-return null;
+return $newNode;
```

<br><br>

### `SimplifyIfReturnBoolRector`

- class: [`Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector`](/rules/code-quality/src/Rector/If_/SimplifyIfReturnBoolRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/If_/SimplifyIfReturnBoolRector/Fixture)

Shortens if return false/true to direct return

```diff
-if (strpos($docToken->getContent(), "\n") === false) {
-    return true;
-}
-
-return false;
+return strpos($docToken->getContent(), "\n") === false;
```

<br><br>

### `SimplifyInArrayValuesRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector`](/rules/code-quality/src/Rector/FuncCall/SimplifyInArrayValuesRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/SimplifyInArrayValuesRector/Fixture)

Removes unneeded `array_values()` in `in_array()` call

```diff
-in_array("key", array_values($array), true);
+in_array("key", $array, true);
```

<br><br>

### `SimplifyRegexPatternRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector`](/rules/code-quality/src/Rector/FuncCall/SimplifyRegexPatternRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/SimplifyRegexPatternRector/Fixture)

Simplify regex pattern to known ranges

```diff
 class SomeClass
 {
     public function run($value)
     {
-        preg_match('#[a-zA-Z0-9+]#', $value);
+        preg_match('#[\w\d+]#', $value);
     }
 }
```

<br><br>

### `SimplifyStrposLowerRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector`](/rules/code-quality/src/Rector/FuncCall/SimplifyStrposLowerRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/SimplifyStrposLowerRector/Fixture)

Simplify strpos(strtolower(), "...") calls

```diff
-strpos(strtolower($var), "...")"
+stripos($var, "...")"
```

<br><br>

### `SimplifyTautologyTernaryRector`

- class: [`Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector`](/rules/code-quality/src/Rector/Ternary/SimplifyTautologyTernaryRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Ternary/SimplifyTautologyTernaryRector/Fixture)

Simplify tautology ternary to value

```diff
-$value = ($fullyQualifiedTypeHint !== $typeHint) ? $fullyQualifiedTypeHint : $typeHint;
+$value = $fullyQualifiedTypeHint;
```

<br><br>

### `SimplifyUselessVariableRector`

- class: [`Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector`](/rules/code-quality/src/Rector/Return_/SimplifyUselessVariableRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Return_/SimplifyUselessVariableRector/Fixture)

Removes useless variable assigns

```diff
 function () {
-    $a = true;
-    return $a;
+    return true;
 };
```

<br><br>

### `SingleInArrayToCompareRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector`](/rules/code-quality/src/Rector/FuncCall/SingleInArrayToCompareRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/FuncCall/SingleInArrayToCompareRector/Fixture)

Changes `in_array()` with single element to ===

```diff
 class SomeClass
 {
     public function run()
     {
-        if (in_array(strtolower($type), ['$this'], true)) {
+        if (strtolower($type) === '$this') {
             return strtolower($type);
         }
     }
 }
```

<br><br>

### `SplitListAssignToSeparateLineRector`

- class: [`Rector\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector`](/rules/code-quality/src/Rector/Assign/SplitListAssignToSeparateLineRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Assign/SplitListAssignToSeparateLineRector/Fixture)

Splits `[$a, $b] = [5, 10]` scalar assign to standalone lines

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        [$a, $b] = [1, 2];
+        $a = 1;
+        $b = 2;
     }
 }
```

<br><br>

### `StrlenZeroToIdenticalEmptyStringRector`

- class: [`Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector`](/rules/code-quality/src/Rector/Identical/StrlenZeroToIdenticalEmptyStringRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Identical/StrlenZeroToIdenticalEmptyStringRector/Fixture)

Changes `strlen` comparison to 0 to direct empty string compare

```diff
 class SomeClass
 {
     public function run($value)
     {
-        $empty = strlen($value) === 0;
+        $empty = $value === '';
     }
 }
```

<br><br>

### `ThrowWithPreviousExceptionRector`

- class: [`Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector`](/rules/code-quality/src/Rector/Catch_/ThrowWithPreviousExceptionRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Catch_/ThrowWithPreviousExceptionRector/Fixture)

When throwing into a catch block, checks that the previous exception is passed to the new throw clause

```diff
 class SomeClass
 {
     public function run()
     {
         try {
             $someCode = 1;
         } catch (Throwable $throwable) {
-            throw new AnotherException('ups');
+            throw new AnotherException('ups', $throwable->getCode(), $throwable);
         }
     }
 }
```

<br><br>

### `UnnecessaryTernaryExpressionRector`

- class: [`Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector`](/rules/code-quality/src/Rector/Ternary/UnnecessaryTernaryExpressionRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Ternary/UnnecessaryTernaryExpressionRector/Fixture)

Remove unnecessary ternary expressions.

```diff
-$foo === $bar ? true : false;
+$foo === $bar;
```

<br><br>

### `UnusedForeachValueToArrayKeysRector`

- class: [`Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector`](/rules/code-quality/src/Rector/Foreach_/UnusedForeachValueToArrayKeysRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Foreach_/UnusedForeachValueToArrayKeysRector/Fixture)

Change foreach with unused `$value` but only `$key,` to `array_keys()`

```diff
 class SomeClass
 {
     public function run()
     {
         $items = [];
-        foreach ($values as $key => $value) {
+        foreach (array_keys($values) as $key) {
             $items[$key] = null;
         }
     }
 }
```

<br><br>

### `UseIdenticalOverEqualWithSameTypeRector`

- class: [`Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector`](/rules/code-quality/src/Rector/Equal/UseIdenticalOverEqualWithSameTypeRector.php)
- [test fixtures](/rules/code-quality/tests/Rector/Equal/UseIdenticalOverEqualWithSameTypeRector/Fixture)

Use ===/!== over ==/!=, it values have the same type

```diff
 class SomeClass
 {
     public function run(int $firstValue, int $secondValue)
     {
-         $isSame = $firstValue == $secondValue;
-         $isDiffernt = $firstValue != $secondValue;
+         $isSame = $firstValue === $secondValue;
+         $isDiffernt = $firstValue !== $secondValue;
     }
 }
```

<br><br>

## CodingStyle

### `AddArrayDefaultToArrayPropertyRector`

- class: [`Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector`](/rules/coding-style/src/Rector/Class_/AddArrayDefaultToArrayPropertyRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Class_/AddArrayDefaultToArrayPropertyRector/Fixture)

Adds array default value to property to prevent foreach over null error

```diff
 class SomeClass
 {
     /**
      * @var int[]
      */
-    private $values;
+    private $values = [];

     public function isEmpty()
     {
-        return $this->values === null;
+        return $this->values === [];
     }
 }
```

<br><br>

### `AnnotateThrowablesRector`

- class: [`Rector\CodingStyle\Rector\Throw_\AnnotateThrowablesRector`](/rules/coding-style/src/Rector/Throw_/AnnotateThrowablesRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Throw_/AnnotateThrowablesRector/Fixture)

Adds @throws DocBlock comments to methods that thrwo \Throwables.

```diff
 class RootExceptionInMethodWithDocblock
 {
     /**
      * This is a comment.
      *
      * @param int $code
+     * @throws \RuntimeException
      */
     public function throwException(int $code)
     {
         throw new \RuntimeException('', $code);
     }
 }
```

<br><br>

### `BinarySwitchToIfElseRector`

- class: [`Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector`](/rules/coding-style/src/Rector/Switch_/BinarySwitchToIfElseRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Switch_/BinarySwitchToIfElseRector/Fixture)

Changes switch with 2 options to if-else

```diff
-switch ($foo) {
-    case 'my string':
-        $result = 'ok';
-    break;
-
-    default:
-        $result = 'not ok';
+if ($foo == 'my string') {
+    $result = 'ok;
+} else {
+    $result = 'not ok';
 }
```

<br><br>

### `CallUserFuncCallToVariadicRector`

- class: [`Rector\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector`](/rules/coding-style/src/Rector/FuncCall/CallUserFuncCallToVariadicRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/FuncCall/CallUserFuncCallToVariadicRector/Fixture)

Replace call_user_func_call with variadic

```diff
 class SomeClass
 {
     public function run()
     {
-        call_user_func_array('some_function', $items);
+        some_function(...$items);
     }
 }
```

<br><br>

### `CamelCaseFunctionNamingToUnderscoreRector`

- class: [`Rector\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector`](/rules/coding-style/src/Rector/Function_/CamelCaseFunctionNamingToUnderscoreRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Function_/CamelCaseFunctionNamingToUnderscoreRector/Fixture)

Change CamelCase naming of functions to under_score naming

```diff
-function someCamelCaseFunction()
+function some_camel_case_function()
 {
 }

-someCamelCaseFunction();
+some_camel_case_function();
```

<br><br>

### `CatchExceptionNameMatchingTypeRector`

- class: [`Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector`](/rules/coding-style/src/Rector/Catch_/CatchExceptionNameMatchingTypeRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Catch_/CatchExceptionNameMatchingTypeRector/Fixture)

Type and name of catch exception should match

```diff
 class SomeClass
 {
     public function run()
     {
         try {
             // ...
-        } catch (SomeException $typoException) {
-            $typoException->getMessage();
+        } catch (SomeException $someException) {
+            $someException->getMessage();
         }
     }
 }
```

<br><br>

### `ConsistentImplodeRector`

- class: [`Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector`](/rules/coding-style/src/Rector/FuncCall/ConsistentImplodeRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/FuncCall/ConsistentImplodeRector/Fixture)

Changes various `implode` forms to consistent one

```diff
 class SomeClass
 {
     public function run(array $items)
     {
-        $itemsAsStrings = implode($items);
-        $itemsAsStrings = implode($items, '|');
+        $itemsAsStrings = implode('', $items);
+        $itemsAsStrings = implode('|', $items);

         $itemsAsStrings = implode('|', $items);
     }
 }
```

<br><br>

### `ConsistentPregDelimiterRector`

- class: [`Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector`](/rules/coding-style/src/Rector/FuncCall/ConsistentPregDelimiterRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/FuncCall/ConsistentPregDelimiterRector/Fixture)

Replace PREG delimiter with configured one

```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ConsistentPregDelimiterRector::class)
        ->call('configure', [[
            ConsistentPregDelimiterRector::DELIMITER => '#'
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        preg_match('~value~', $value);
-        preg_match_all('~value~im', $value);
+        preg_match('#value#', $value);
+        preg_match_all('#value#im', $value);
     }
 }
```

<br><br>

### `EncapsedStringsToSprintfRector`

- class: [`Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector`](/rules/coding-style/src/Rector/Encapsed/EncapsedStringsToSprintfRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Encapsed/EncapsedStringsToSprintfRector/Fixture)

Convert enscaped {$string} to more readable `sprintf`

```diff
 final class SomeClass
 {
     public function run(string $format)
     {
-        return "Unsupported format {$format}";
+        return sprintf('Unsupported format %s', $format);
     }
 }
```

<br><br>

### `FollowRequireByDirRector`

- class: [`Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector`](/rules/coding-style/src/Rector/Include_/FollowRequireByDirRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Include_/FollowRequireByDirRector/Fixture)

include/require should be followed by absolute path

```diff
 class SomeClass
 {
     public function run()
     {
-        require 'autoload.php';
+        require __DIR__ . '/autoload.php';
     }
 }
```

<br><br>

### `FunctionCallToConstantRector`

- class: [`Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector`](/rules/coding-style/src/Rector/FuncCall/FunctionCallToConstantRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/FuncCall/FunctionCallToConstantRector/Fixture)

Changes use of function calls to use constants

```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FunctionCallToConstantRector::class)
        ->call(
            'configure',
            [[
                FunctionCallToConstantRector::FUNCTIONS_TO_CONSTANTS => [
                    'php_sapi_name' => 'PHP_SAPI',
                ],
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = php_sapi_name();
+        $value = PHP_SAPI;
     }
 }
```
```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FunctionCallToConstantRector::class)
        ->call('configure', [[
            FunctionCallToConstantRector::FUNCTIONS_TO_CONSTANTS => [
                'pi' => 'M_PI',
            ],
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = pi();
+        $value = M_PI;
     }
 }
```

<br><br>

### `MakeInheritedMethodVisibilitySameAsParentRector`

- class: [`Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector`](/rules/coding-style/src/Rector/ClassMethod/MakeInheritedMethodVisibilitySameAsParentRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/ClassMethod/MakeInheritedMethodVisibilitySameAsParentRector/Fixture)

Make method visibility same as parent one

```diff
 class ChildClass extends ParentClass
 {
-    public function run()
+    protected function run()
     {
     }
 }

 class ParentClass
 {
     protected function run()
     {
     }
 }
```

<br><br>

### `ManualJsonStringToJsonEncodeArrayRector`

- class: [`Rector\CodingStyle\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector`](/rules/coding-style/src/Rector/Assign/ManualJsonStringToJsonEncodeArrayRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Assign/ManualJsonStringToJsonEncodeArrayRector/Fixture)

Add extra space before new assign set

```diff
 final class SomeClass
 {
     public function run()
     {
-        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"10"}}';
+        $data = [
+            'role_name' => 'admin',
+            'numberz' => ['id' => 10]
+        ];
+
+        $someJsonAsString = Nette\Utils\Json::encode($data);
     }
 }
```

<br><br>

### `NewlineBeforeNewAssignSetRector`

- class: [`Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector`](/rules/coding-style/src/Rector/ClassMethod/NewlineBeforeNewAssignSetRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/ClassMethod/NewlineBeforeNewAssignSetRector/Fixture)

Add extra space before new assign set

```diff
 final class SomeClass
 {
     public function run()
     {
         $value = new Value;
         $value->setValue(5);
+
         $value2 = new Value;
         $value2->setValue(1);
     }
 }
```

<br><br>

### `NullableCompareToNullRector`

- class: [`Rector\CodingStyle\Rector\If_\NullableCompareToNullRector`](/rules/coding-style/src/Rector/If_/NullableCompareToNullRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/If_/NullableCompareToNullRector/Fixture)

Changes negate of empty comparison of nullable value to explicit === or !== compare

```diff
 /** @var stdClass|null $value */
-if ($value) {
+if ($value !== null) {
 }

-if (!$value) {
+if ($value === null) {
 }
```

<br><br>

### `PreferThisOrSelfMethodCallRector`

- class: [`Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector`](/rules/coding-style/src/Rector/MethodCall/PreferThisOrSelfMethodCallRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/MethodCall/PreferThisOrSelfMethodCallRector/Fixture)

Changes `$this->...` to self:: or vise versa for specific types

```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PreferThisOrSelfMethodCallRector::class)
        ->call('configure', [[
            PreferThisOrSelfMethodCallRector::TYPE_TO_PREFERENCE => [
                'PHPUnit\TestCase' => 'self',
            ],
        ]]);
};
```

↓

```diff
 class SomeClass extends PHPUnit\TestCase
 {
     public function run()
     {
-        $this->assertThis();
+        self::assertThis();
     }
 }
```

<br><br>

### `RemoveDoubleUnderscoreInMethodNameRector`

- class: [`Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector`](/rules/coding-style/src/Rector/ClassMethod/RemoveDoubleUnderscoreInMethodNameRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/ClassMethod/RemoveDoubleUnderscoreInMethodNameRector/Fixture)

Non-magic PHP object methods cannot start with "__"

```diff
 class SomeClass
 {
-    public function __getName($anotherObject)
+    public function getName($anotherObject)
     {
-        $anotherObject->__getSurname();
+        $anotherObject->getSurname();
     }
 }
```

<br><br>

### `RemoveUnusedAliasRector`

- class: [`Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector`](/rules/coding-style/src/Rector/Use_/RemoveUnusedAliasRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Use_/RemoveUnusedAliasRector/Fixture)

Removes unused use aliases. Keep annotation aliases like "Doctrine\ORM\Mapping as ORM" to keep convention format

```diff
-use Symfony\Kernel as BaseKernel;
+use Symfony\Kernel;

-class SomeClass extends BaseKernel
+class SomeClass extends Kernel
 {
 }
```

<br><br>

### `ReturnArrayClassMethodToYieldRector`

- class: [`Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector`](/rules/coding-style/src/Rector/ClassMethod/ReturnArrayClassMethodToYieldRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/ClassMethod/ReturnArrayClassMethodToYieldRector/Fixture)

Turns array return to yield return in specific type and method

```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReturnArrayClassMethodToYieldRector::class)
        ->call(
            'configure',
            [[
                ReturnArrayClassMethodToYieldRector::METHODS_TO_YIELDS => inline_value_objects(
                                [new ReturnArrayClassMethodToYield('EventSubscriberInterface', 'getSubscribedEvents')]
                            )
            ]]
        );
};
```

↓

```diff
 class SomeEventSubscriber implements EventSubscriberInterface
 {
     public static function getSubscribedEvents()
     {
-        return ['event' => 'callback'];
+        yield 'event' => 'callback';
     }
 }
```

<br><br>

### `SplitDoubleAssignRector`

- class: [`Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector`](/rules/coding-style/src/Rector/Assign/SplitDoubleAssignRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Assign/SplitDoubleAssignRector/Fixture)

Split multiple inline assigns to `each` own lines default value, to prevent undefined array issues

```diff
 class SomeClass
 {
     public function run()
     {
-        $one = $two = 1;
+        $one = 1;
+        $two = 1;
     }
 }
```

<br><br>

### `SplitGroupedConstantsAndPropertiesRector`

- class: [`Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector`](/rules/coding-style/src/Rector/ClassConst/SplitGroupedConstantsAndPropertiesRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/ClassConst/SplitGroupedConstantsAndPropertiesRector/Fixture)

Separate constant and properties to own lines

```diff
 class SomeClass
 {
-    const HI = true, AHOJ = 'true';
+    const HI = true;
+    const AHOJ = 'true';

     /**
      * @var string
      */
-    public $isIt, $isIsThough;
+    public $isIt;
+
+    /**
+     * @var string
+     */
+    public $isIsThough;
 }
```

<br><br>

### `SplitGroupedUseImportsRector`

- class: [`Rector\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector`](/rules/coding-style/src/Rector/Use_/SplitGroupedUseImportsRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Use_/SplitGroupedUseImportsRector/Fixture)

Split grouped use imports and trait statements to standalone lines

```diff
-use A, B;
+use A;
+use B;

 class SomeClass
 {
-    use SomeTrait, AnotherTrait;
+    use SomeTrait;
+    use AnotherTrait;
 }
```

<br><br>

### `SplitStringClassConstantToClassConstFetchRector`

- class: [`Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector`](/rules/coding-style/src/Rector/String_/SplitStringClassConstantToClassConstFetchRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/String_/SplitStringClassConstantToClassConstFetchRector/Fixture)

Separate class constant in a string to class constant fetch and string

```diff
 class SomeClass
 {
     const HI = true;
 }

 class AnotherClass
 {
     public function get()
     {
-        return 'SomeClass::HI';
+        return SomeClass::class . '::HI';
     }
 }
```

<br><br>

### `StrictArraySearchRector`

- class: [`Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector`](/rules/coding-style/src/Rector/FuncCall/StrictArraySearchRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/FuncCall/StrictArraySearchRector/Fixture)

Makes `array_search` search for identical elements

```diff
-array_search($value, $items);
+array_search($value, $items, true);
```

<br><br>

### `SymplifyQuoteEscapeRector`

- class: [`Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector`](/rules/coding-style/src/Rector/String_/SymplifyQuoteEscapeRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/String_/SymplifyQuoteEscapeRector/Fixture)

Prefer quote that are not inside the string

```diff
 class SomeClass
 {
     public function run()
     {
-         $name = "\" Tom";
-         $name = '\' Sara';
+         $name = '" Tom';
+         $name = "' Sara";
     }
 }
```

<br><br>

### `TernaryConditionVariableAssignmentRector`

- class: [`Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector`](/rules/coding-style/src/Rector/Ternary/TernaryConditionVariableAssignmentRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Ternary/TernaryConditionVariableAssignmentRector/Fixture)

Assign outcome of ternary condition to variable, where applicable

```diff
 function ternary($value)
 {
-    $value ? $a = 1 : $a = 0;
+    $a = $value ? 1 : 0;
 }
```

<br><br>

### `UnderscoreToCamelCaseLocalVariableNameRector`

- class: [`Rector\CodingStyle\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector`](/rules/coding-style/src/Rector/Variable/UnderscoreToCamelCaseLocalVariableNameRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Variable/UnderscoreToCamelCaseLocalVariableNameRector/Fixture)

Change under_score local variable names to camelCase

```diff
 final class SomeClass
 {
     public function run($a_b)
     {
-        $some_value = $a_b;
+        $someValue = $a_b;
     }
 }
```

<br><br>

### `UseClassKeywordForClassNameResolutionRector`

- class: [`Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector`](/rules/coding-style/src/Rector/String_/UseClassKeywordForClassNameResolutionRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/String_/UseClassKeywordForClassNameResolutionRector/Fixture)

Use `class` keyword for class name resolution in string instead of hardcoded string reference

```diff
-$value = 'App\SomeClass::someMethod()';
+$value = \App\SomeClass . '::someMethod()';
```

<br><br>

### `UseIncrementAssignRector`

- class: [`Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector`](/rules/coding-style/src/Rector/Plus/UseIncrementAssignRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Plus/UseIncrementAssignRector/Fixture)

Use ++ increment instead of `$var += 1`

```diff
 class SomeClass
 {
     public function run()
     {
-        $style += 1;
+        ++$style
     }
 }
```

<br><br>

### `UseMessageVariableForSprintfInSymfonyStyleRector`

- class: [`Rector\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector`](/rules/coding-style/src/Rector/MethodCall/UseMessageVariableForSprintfInSymfonyStyleRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/MethodCall/UseMessageVariableForSprintfInSymfonyStyleRector/Fixture)

Decouple `$message` property from `sprintf()` calls in `$this->smyfonyStyle->method()`

```diff
 use Symfony\Component\Console\Style\SymfonyStyle;

 final class SomeClass
 {
     public function run(SymfonyStyle $symfonyStyle)
     {
-        $symfonyStyle->info(sprintf('Hi %s', 'Tom'));
+        $message = sprintf('Hi %s', 'Tom');
+        $symfonyStyle->info($message);
     }
 }
```

<br><br>

### `VarConstantCommentRector`

- class: [`Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector`](/rules/coding-style/src/Rector/ClassConst/VarConstantCommentRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/ClassConst/VarConstantCommentRector/Fixture)

`Constant` should have a @var comment with type

```diff
 class SomeClass
 {
+    /**
+     * @var string
+     */
     const HI = 'hi';
 }
```

<br><br>

### `VersionCompareFuncCallToConstantRector`

- class: [`Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector`](/rules/coding-style/src/Rector/FuncCall/VersionCompareFuncCallToConstantRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/FuncCall/VersionCompareFuncCallToConstantRector/Fixture)

Changes use of call to version compare function to use of PHP version constant

```diff
 class SomeClass
 {
     public function run()
     {
-        version_compare(PHP_VERSION, '5.3.0', '<');
+        PHP_VERSION_ID < 50300;
     }
 }
```

<br><br>

### `WrapEncapsedVariableInCurlyBracesRector`

- class: [`Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector`](/rules/coding-style/src/Rector/Encapsed/WrapEncapsedVariableInCurlyBracesRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/Encapsed/WrapEncapsedVariableInCurlyBracesRector/Fixture)

Wrap encapsed variables in curly braces

```diff
 function run($world)
 {
-    echo "Hello $world!"
+    echo "Hello {$world}!"
 }
```

<br><br>

### `YieldClassMethodToArrayClassMethodRector`

- class: [`Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector`](/rules/coding-style/src/Rector/ClassMethod/YieldClassMethodToArrayClassMethodRector.php)
- [test fixtures](/rules/coding-style/tests/Rector/ClassMethod/YieldClassMethodToArrayClassMethodRector/Fixture)

Turns yield return to array return in specific type and method

```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(YieldClassMethodToArrayClassMethodRector::class)
        ->call(
            'configure',
            [[
                YieldClassMethodToArrayClassMethodRector::METHODS_BY_TYPE => [
                    'EventSubscriberInterface' => [
                        'getSubscribedEvents',
                    ],
                ],
            ]]
        );
};
```

↓

```diff
 class SomeEventSubscriber implements EventSubscriberInterface
 {
     public static function getSubscribedEvents()
     {
-        yield 'event' => 'callback';
+        return ['event' => 'callback'];
     }
 }
```

<br><br>

## DeadCode

### `RemoveAlwaysTrueIfConditionRector`

- class: [`Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector`](/rules/dead-code/src/Rector/If_/RemoveAlwaysTrueIfConditionRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/If_/RemoveAlwaysTrueIfConditionRector/Fixture)

Remove if condition that is always true

```diff
 final class SomeClass
 {
     public function go()
     {
-        if (1 === 1) {
-            return 'yes';
-        }
+        return 'yes';

         return 'no';
     }
 }
```

<br><br>

### `RemoveAndTrueRector`

- class: [`Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector`](/rules/dead-code/src/Rector/BooleanAnd/RemoveAndTrueRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/BooleanAnd/RemoveAndTrueRector/Fixture)

Remove and true that has no added value

```diff
 class SomeClass
 {
     public function run()
     {
-        return true && 5 === 1;
+        return 5 === 1;
     }
 }
```

<br><br>

### `RemoveAssignOfVoidReturnFunctionRector`

- class: [`Rector\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector`](/rules/dead-code/src/Rector/Assign/RemoveAssignOfVoidReturnFunctionRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Assign/RemoveAssignOfVoidReturnFunctionRector/Fixture)

Remove assign of void function/method to variable

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = $this->getOne();
+        $this->getOne();
     }

     private function getOne(): void
     {
     }
 }
```

<br><br>

### `RemoveCodeAfterReturnRector`

- class: [`Rector\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector`](/rules/dead-code/src/Rector/FunctionLike/RemoveCodeAfterReturnRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/FunctionLike/RemoveCodeAfterReturnRector/Fixture)

Remove dead code after return statement

```diff
 class SomeClass
 {
     public function run(int $a)
     {
          return $a;
-         $a++;
     }
 }
```

<br><br>

### `RemoveConcatAutocastRector`

- class: [`Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector`](/rules/dead-code/src/Rector/Concat/RemoveConcatAutocastRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Concat/RemoveConcatAutocastRector/Fixture)

Remove (string) casting when it comes to concat, that does this by default

```diff
 class SomeConcatingClass
 {
     public function run($value)
     {
-        return 'hi ' . (string) $value;
+        return 'hi ' . $value;
     }
 }
```

<br><br>

### `RemoveDeadConstructorRector`

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector`](/rules/dead-code/src/Rector/ClassMethod/RemoveDeadConstructorRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/ClassMethod/RemoveDeadConstructorRector/Fixture)

Remove empty constructor

```diff
 class SomeClass
 {
-    public function __construct()
-    {
-    }
 }
```

<br><br>

### `RemoveDeadIfForeachForRector`

- class: [`Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector`](/rules/dead-code/src/Rector/For_/RemoveDeadIfForeachForRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/For_/RemoveDeadIfForeachForRector/Fixture)

Remove if, foreach and for that does not do anything

```diff
 class SomeClass
 {
     public function run($someObject)
     {
         $value = 5;
-        if ($value) {
-        }
-
         if ($someObject->run()) {
-        }
-
-        foreach ($values as $value) {
         }

         return $value;
     }
 }
```

<br><br>

### `RemoveDeadRecursiveClassMethodRector`

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector`](/rules/dead-code/src/Rector/ClassMethod/RemoveDeadRecursiveClassMethodRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/ClassMethod/RemoveDeadRecursiveClassMethodRector/Fixture)

Remove unused public method that only calls itself recursively

```diff
 class SomeClass
 {
-    public function run()
-    {
-        return $this->run();
-    }
 }
```

<br><br>

### `RemoveDeadReturnRector`

- class: [`Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector`](/rules/dead-code/src/Rector/FunctionLike/RemoveDeadReturnRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/FunctionLike/RemoveDeadReturnRector/Fixture)

Remove last return in the functions, since does not do anything

```diff
 class SomeClass
 {
     public function run()
     {
         $shallWeDoThis = true;

         if ($shallWeDoThis) {
             return;
         }
-
-        return;
     }
 }
```

<br><br>

### `RemoveDeadStmtRector`

- class: [`Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector`](/rules/dead-code/src/Rector/Expression/RemoveDeadStmtRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Expression/RemoveDeadStmtRector/Fixture)

Removes dead code statements

```diff
-$value = 5;
-$value;
+$value = 5;
```

<br><br>

### `RemoveDeadTryCatchRector`

- class: [`Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector`](/rules/dead-code/src/Rector/TryCatch/RemoveDeadTryCatchRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/TryCatch/RemoveDeadTryCatchRector/Fixture)

Remove dead try/catch

```diff
 class SomeClass
 {
     public function run()
     {
-        try {
-            // some code
-        }
-        catch (Throwable $throwable) {
-            throw $throwable;
-        }
+        // some code
     }
 }
```

<br><br>

### `RemoveDeadZeroAndOneOperationRector`

- class: [`Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector`](/rules/dead-code/src/Rector/Plus/RemoveDeadZeroAndOneOperationRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Plus/RemoveDeadZeroAndOneOperationRector/Fixture)

Remove operation with 1 and 0, that have no effect on the value

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = 5 * 1;
-        $value = 5 + 0;
+        $value = 5;
+        $value = 5;
     }
 }
```

<br><br>

### `RemoveDefaultArgumentValueRector`

- class: [`Rector\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector`](/rules/dead-code/src/Rector/MethodCall/RemoveDefaultArgumentValueRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/MethodCall/RemoveDefaultArgumentValueRector/Fixture)

Remove argument value, if it is the same as default value

```diff
 class SomeClass
 {
     public function run()
     {
-        $this->runWithDefault([]);
-        $card = self::runWithStaticDefault([]);
+        $this->runWithDefault();
+        $card = self::runWithStaticDefault();
     }

     public function runWithDefault($items = [])
     {
         return $items;
     }

     public function runStaticWithDefault($cards = [])
     {
         return $cards;
     }
 }
```

<br><br>

### `RemoveDelegatingParentCallRector`

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector`](/rules/dead-code/src/Rector/ClassMethod/RemoveDelegatingParentCallRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/ClassMethod/RemoveDelegatingParentCallRector/Fixture)

Removed dead parent call, that does not change anything

```diff
 class SomeClass
 {
-    public function prettyPrint(array $stmts): string
-    {
-        return parent::prettyPrint($stmts);
-    }
 }
```

<br><br>

### `RemoveDoubleAssignRector`

- class: [`Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector`](/rules/dead-code/src/Rector/Assign/RemoveDoubleAssignRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Assign/RemoveDoubleAssignRector/Fixture)

Simplify useless double assigns

```diff
-$value = 1;
 $value = 1;
```

<br><br>

### `RemoveDuplicatedArrayKeyRector`

- class: [`Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector`](/rules/dead-code/src/Rector/Array_/RemoveDuplicatedArrayKeyRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Array_/RemoveDuplicatedArrayKeyRector/Fixture)

Remove duplicated `key` in defined arrays.

```diff
 $item = [
-    1 => 'A',
     1 => 'B'
 ];
```

<br><br>

### `RemoveDuplicatedCaseInSwitchRector`

- class: [`Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector`](/rules/dead-code/src/Rector/Switch_/RemoveDuplicatedCaseInSwitchRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Switch_/RemoveDuplicatedCaseInSwitchRector/Fixture)

2 following switch keys with identical  will be reduced to one result

```diff
 class SomeClass
 {
     public function run()
     {
         switch ($name) {
              case 'clearHeader':
                  return $this->modifyHeader($node, 'remove');
              case 'clearAllHeaders':
-                 return $this->modifyHeader($node, 'replace');
              case 'clearRawHeaders':
                  return $this->modifyHeader($node, 'replace');
              case '...':
                  return 5;
         }
     }
 }
```

<br><br>

### `RemoveDuplicatedIfReturnRector`

- class: [`Rector\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector`](/rules/dead-code/src/Rector/FunctionLike/RemoveDuplicatedIfReturnRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/FunctionLike/RemoveDuplicatedIfReturnRector/Fixture)

Remove duplicated if stmt with return in function/method body

```diff
 class SomeClass
 {
     public function run($value)
     {
         if ($value) {
             return true;
         }

         $value2 = 100;
-
-        if ($value) {
-            return true;
-        }
     }
 }
```

<br><br>

### `RemoveDuplicatedInstanceOfRector`

- class: [`Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector`](/rules/dead-code/src/Rector/BinaryOp/RemoveDuplicatedInstanceOfRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/BinaryOp/RemoveDuplicatedInstanceOfRector/Fixture)

Remove duplicated instanceof in one call

```diff
 class SomeClass
 {
-    public function run($value)
+    public function run($value): void
     {
-        $isIt = $value instanceof A || $value instanceof A;
-        $isIt = $value instanceof A && $value instanceof A;
+        $isIt = $value instanceof A;
+        $isIt = $value instanceof A;
     }
 }
```

<br><br>

### `RemoveEmptyClassMethodRector`

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector`](/rules/dead-code/src/Rector/ClassMethod/RemoveEmptyClassMethodRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/ClassMethod/RemoveEmptyClassMethodRector/Fixture)

Remove empty method calls not required by parents

```diff
 class OrphanClass
 {
-    public function __construct()
-    {
-    }
 }
```

<br><br>

### `RemoveNullPropertyInitializationRector`

- class: [`Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector`](/rules/dead-code/src/Rector/PropertyProperty/RemoveNullPropertyInitializationRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/PropertyProperty/RemoveNullPropertyInitializationRector/Fixture)

Remove initialization with null value from property declarations

```diff
 class SunshineCommand extends ParentClassWithNewConstructor
 {
-    private $myVar = null;
+    private $myVar;
 }
```

<br><br>

### `RemoveOverriddenValuesRector`

- class: [`Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector`](/rules/dead-code/src/Rector/FunctionLike/RemoveOverriddenValuesRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/FunctionLike/RemoveOverriddenValuesRector/Fixture)

Remove initial assigns of overridden values

```diff
 final class SomeController
 {
     public function run()
     {
-         $directories = [];
          $possibleDirectories = [];
          $directories = array_filter($possibleDirectories, 'file_exists');
     }
 }
```

<br><br>

### `RemoveParentCallWithoutParentRector`

- class: [`Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector`](/rules/dead-code/src/Rector/StaticCall/RemoveParentCallWithoutParentRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/StaticCall/RemoveParentCallWithoutParentRector/Fixture)

Remove unused parent call with no parent class

```diff
 class OrphanClass
 {
     public function __construct()
     {
-         parent::__construct();
     }
 }
```

<br><br>

### `RemoveSetterOnlyPropertyAndMethodCallRector`

- class: [`Rector\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector`](/rules/dead-code/src/Rector/Property/RemoveSetterOnlyPropertyAndMethodCallRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Property/RemoveSetterOnlyPropertyAndMethodCallRector/Fixture)

Removes method that set values that are never used

```diff
 class SomeClass
 {
-    private $name;
-
-    public function setName($name)
-    {
-        $this->name = $name;
-    }
 }

 class ActiveOnlySetter
 {
     public function run()
     {
         $someClass = new SomeClass();
-        $someClass->setName('Tom');
     }
 }
```

<br><br>

### `RemoveUnreachableStatementRector`

- class: [`Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector`](/rules/dead-code/src/Rector/Stmt/RemoveUnreachableStatementRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Stmt/RemoveUnreachableStatementRector/Fixture)

Remove unreachable statements

```diff
 class SomeClass
 {
     public function run()
     {
         return 5;
-
-        $removeMe = 10;
     }
 }
```

<br><br>

### `RemoveUnusedAssignVariableRector`

- class: [`Rector\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector`](/rules/dead-code/src/Rector/Assign/RemoveUnusedAssignVariableRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Assign/RemoveUnusedAssignVariableRector/Fixture)

Remove assigned unused variable

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = $this->process();
+        $this->process();
     }

     public function process()
     {
         // something going on
         return 5;
     }
 }
```

<br><br>

### `RemoveUnusedClassConstantRector`

- class: [`Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector`](/rules/dead-code/src/Rector/ClassConst/RemoveUnusedClassConstantRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/ClassConst/RemoveUnusedClassConstantRector/Fixture)

Remove unused class constants

```diff
 class SomeClass
 {
-    private const SOME_CONST = 'dead';
-
     public function run()
     {
     }
 }
```

<br><br>

### `RemoveUnusedClassesRector`

- class: [`Rector\DeadCode\Rector\Class_\RemoveUnusedClassesRector`](/rules/dead-code/src/Rector/Class_/RemoveUnusedClassesRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Class_/RemoveUnusedClassesRector/Fixture)

Remove unused classes without interface

```diff
 interface SomeInterface
 {
 }

 class SomeClass implements SomeInterface
 {
     public function run($items)
     {
         return null;
     }
-}
-
-class NowhereUsedClass
-{
 }
```

<br><br>

### `RemoveUnusedDoctrineEntityMethodAndPropertyRector`

- class: [`Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector`](/rules/dead-code/src/Rector/Class_/RemoveUnusedDoctrineEntityMethodAndPropertyRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Class_/RemoveUnusedDoctrineEntityMethodAndPropertyRector/Fixture)

Removes unused methods and properties from Doctrine entity classes

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class UserEntity
 {
-    /**
-     * @ORM\Column
-     */
-    private $name;
-
-    public function getName()
-    {
-        return $this->name;
-    }
-
-    public function setName($name)
-    {
-        $this->name = $name;
-    }
 }
```

<br><br>

### `RemoveUnusedForeachKeyRector`

- class: [`Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector`](/rules/dead-code/src/Rector/Foreach_/RemoveUnusedForeachKeyRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Foreach_/RemoveUnusedForeachKeyRector/Fixture)

Remove unused `key` in foreach

```diff
 $items = [];
-foreach ($items as $key => $value) {
+foreach ($items as $value) {
     $result = $value;
 }
```

<br><br>

### `RemoveUnusedFunctionRector`

- class: [`Rector\DeadCode\Rector\Function_\RemoveUnusedFunctionRector`](/rules/dead-code/src/Rector/Function_/RemoveUnusedFunctionRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Function_/RemoveUnusedFunctionRector/Fixture)

Remove unused function

```diff
-function removeMe()
-{
-}
-
 function useMe()
 {
 }

 useMe();
```

<br><br>

### `RemoveUnusedNonEmptyArrayBeforeForeachRector`

- class: [`Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector`](/rules/dead-code/src/Rector/If_/RemoveUnusedNonEmptyArrayBeforeForeachRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/If_/RemoveUnusedNonEmptyArrayBeforeForeachRector/Fixture)

Remove unused if check to non-empty array before foreach of the array

```diff
 class SomeClass
 {
     public function run()
     {
         $values = [];
-        if ($values !== []) {
-            foreach ($values as $value) {
-                echo $value;
-            }
+        foreach ($values as $value) {
+            echo $value;
         }
     }
 }
```

<br><br>

### `RemoveUnusedParameterRector`

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector`](/rules/dead-code/src/Rector/ClassMethod/RemoveUnusedParameterRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/ClassMethod/RemoveUnusedParameterRector/Fixture)

Remove unused parameter, if not required by interface or parent class

```diff
 class SomeClass
 {
-    public function __construct($value, $value2)
+    public function __construct($value)
     {
          $this->value = $value;
     }
 }
```

<br><br>

### `RemoveUnusedPrivateConstantRector`

- class: [`Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector`](/rules/dead-code/src/Rector/ClassConst/RemoveUnusedPrivateConstantRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/ClassConst/RemoveUnusedPrivateConstantRector/Fixture)

Remove unused private constant

```diff
 final class SomeController
 {
-    private const SOME_CONSTANT = 5;
     public function run()
     {
         return 5;
     }
 }
```

<br><br>

### `RemoveUnusedPrivateMethodRector`

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector`](/rules/dead-code/src/Rector/ClassMethod/RemoveUnusedPrivateMethodRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/ClassMethod/RemoveUnusedPrivateMethodRector/Fixture)

Remove unused private method

```diff
 final class SomeController
 {
     public function run()
     {
         return 5;
     }
-
-    private function skip()
-    {
-        return 10;
-    }
 }
```

<br><br>

### `RemoveUnusedPrivatePropertyRector`

- class: [`Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector`](/rules/dead-code/src/Rector/Property/RemoveUnusedPrivatePropertyRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Property/RemoveUnusedPrivatePropertyRector/Fixture)

Remove unused private properties

```diff
 class SomeClass
 {
-    private $property;
 }
```

<br><br>

### `RemoveUnusedVariableAssignRector`

- class: [`Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector`](/rules/dead-code/src/Rector/Assign/RemoveUnusedVariableAssignRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Assign/RemoveUnusedVariableAssignRector/Fixture)

Remove unused assigns to variables

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = 5;
     }
 }
```

<br><br>

### `SimplifyIfElseWithSameContentRector`

- class: [`Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector`](/rules/dead-code/src/Rector/If_/SimplifyIfElseWithSameContentRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/If_/SimplifyIfElseWithSameContentRector/Fixture)

Remove if/else if they have same content

```diff
 class SomeClass
 {
     public function run()
     {
-        if (true) {
-            return 1;
-        } else {
-            return 1;
-        }
+        return 1;
     }
 }
```

<br><br>

### `SimplifyMirrorAssignRector`

- class: [`Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector`](/rules/dead-code/src/Rector/Expression/SimplifyMirrorAssignRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Expression/SimplifyMirrorAssignRector/Fixture)

Removes unneeded $a = $a assigns

```diff
-$a = $a;
```

<br><br>

### `TernaryToBooleanOrFalseToBooleanAndRector`

- class: [`Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector`](/rules/dead-code/src/Rector/Ternary/TernaryToBooleanOrFalseToBooleanAndRector.php)
- [test fixtures](/rules/dead-code/tests/Rector/Ternary/TernaryToBooleanOrFalseToBooleanAndRector/Fixture)

Change ternary of bool : false to && bool

```diff
 class SomeClass
 {
     public function go()
     {
-        return $value ? $this->getBool() : false;
+        return $value && $this->getBool();
     }

     private function getBool(): bool
     {
         return (bool) 5;
     }
 }
```

<br><br>

## Decouple

### `DecoupleClassMethodToOwnClassRector`

- class: [`Rector\Decouple\Rector\ClassMethod\DecoupleClassMethodToOwnClassRector`](/rules/decouple/src/Rector/ClassMethod/DecoupleClassMethodToOwnClassRector.php)
- [test fixtures](/rules/decouple/tests/Rector/ClassMethod/DecoupleClassMethodToOwnClassRector/Fixture)

Move class method with its all dependencies to own class by method name

```php
<?php

declare(strict_types=1);

use Rector\Decouple\Rector\ClassMethod\DecoupleClassMethodToOwnClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DecoupleClassMethodToOwnClassRector::class)
        ->call(
            'configure',
            [[
                DecoupleClassMethodToOwnClassRector::METHOD_NAMES_BY_CLASS => [
                    'SomeClass' => [
                        'someMethod' => [
                            'class' => 'NewDecoupledClass',
                            'method' => 'someRenamedMethod',
                            'parent_class' => 'AddedParentClass',
                        ],
                    ],
                ],
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
-    public function someMethod()
-    {
-        $this->alsoCallThis();
-    }
-
-    private function alsoCallThis()
-    {
-    }
 }
```

**New file**
```php
<?php

declare(strict_types=1);

class NewDecoupledClass extends AddedParentClass
{
    public function someRenamedMethod(): void
    {
        $this->alsoCallThis();
    }

    private function alsoCallThis(): void
    {
    }
}
```

<br><br>

## Doctrine

### `AddEntityIdByConditionRector`

- class: [`Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector`](/rules/doctrine/src/Rector/Class_/AddEntityIdByConditionRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Class_/AddEntityIdByConditionRector/Fixture)

Add entity id with annotations when meets condition

```php
<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddEntityIdByConditionRector::class);
};
```

↓

```diff
 class SomeClass
 {
     use SomeTrait;
+
+    /**
+      * @ORM\Id
+      * @ORM\Column(type="integer")
+      * @ORM\GeneratedValue(strategy="AUTO")
+      */
+     private $id;
+
+    public function getId(): int
+    {
+        return $this->id;
+    }
 }
```

<br><br>

### `AddUuidAnnotationsToIdPropertyRector`

- class: [`Rector\Doctrine\Rector\Property\AddUuidAnnotationsToIdPropertyRector`](/rules/doctrine/src/Rector/Property/AddUuidAnnotationsToIdPropertyRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Property/AddUuidAnnotationsToIdPropertyRector/Fixture)

Add uuid annotations to `$id` property

```diff
 use Doctrine\ORM\Attributes as ORM;

 /**
  * @ORM\Entity
  */
 class SomeClass
 {
     /**
-     * @var int
+     * @var \Ramsey\Uuid\UuidInterface
      * @ORM\Id
-     * @ORM\Column(type="integer")
-     * @ORM\GeneratedValue(strategy="AUTO")
-     * @Serializer\Type("int")
+     * @ORM\Column(type="uuid_binary")
+     * @Serializer\Type("string")
      */
     public $id;
 }
```

<br><br>

### `AddUuidMirrorForRelationPropertyRector`

- class: [`Rector\Doctrine\Rector\Class_\AddUuidMirrorForRelationPropertyRector`](/rules/doctrine/src/Rector/Class_/AddUuidMirrorForRelationPropertyRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Class_/AddUuidMirrorForRelationPropertyRector/Fixture)

Adds `$uuid` property to entities, that already have `$id` with integer type.Require for step-by-step migration from int to uuid.

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SomeEntity
 {
     /**
      * @ORM\ManyToOne(targetEntity="AnotherEntity", cascade={"persist", "merge"})
      * @ORM\JoinColumn(nullable=false)
      */
     private $amenity;
+
+    /**
+     * @ORM\ManyToOne(targetEntity="AnotherEntity", cascade={"persist", "merge"})
+     * @ORM\JoinColumn(nullable=true, referencedColumnName="uuid")
+     */
+    private $amenityUuid;
 }

 /**
  * @ORM\Entity
  */
 class AnotherEntity
 {
     /**
      * @var int
      * @ORM\Id
      * @ORM\Column(type="integer")
      * @ORM\GeneratedValue(strategy="AUTO")
      */
     private $id;

     private $uuid;
 }
```

<br><br>

### `AddUuidToEntityWhereMissingRector`

- class: [`Rector\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector`](/rules/doctrine/src/Rector/Class_/AddUuidToEntityWhereMissingRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Class_/AddUuidToEntityWhereMissingRector/Fixture)

Adds `$uuid` property to entities, that already have `$id` with integer type.Require for step-by-step migration from int to uuid. In following step it should be renamed to `$id` and replace it

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SomeEntityWithIntegerId
 {
     /**
+     * @var \Ramsey\Uuid\UuidInterface
+     * @ORM\Column(type="uuid_binary", unique=true, nullable=true)
+     */
+    private $uuid;
+    /**
      * @var int
      * @ORM\Id
      * @ORM\Column(type="integer")
      * @ORM\GeneratedValue(strategy="AUTO")
      */
     private $id;
 }
```

<br><br>

### `AlwaysInitializeUuidInEntityRector`

- class: [`Rector\Doctrine\Rector\Class_\AlwaysInitializeUuidInEntityRector`](/rules/doctrine/src/Rector/Class_/AlwaysInitializeUuidInEntityRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Class_/AlwaysInitializeUuidInEntityRector/Fixture)

Add uuid initializion to all entities that misses it

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class AddUuidInit
 {
     /**
      * @ORM\Id
      * @var UuidInterface
      */
     private $superUuid;
+    public function __construct()
+    {
+        $this->superUuid = \Ramsey\Uuid\Uuid::uuid4();
+    }
 }
```

<br><br>

### `ChangeGetIdTypeToUuidRector`

- class: [`Rector\Doctrine\Rector\ClassMethod\ChangeGetIdTypeToUuidRector`](/rules/doctrine/src/Rector/ClassMethod/ChangeGetIdTypeToUuidRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/ClassMethod/ChangeGetIdTypeToUuidRector/Fixture)

Change return type of `getId()` to uuid interface

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class GetId
 {
-    public function getId(): int
+    public function getId(): \Ramsey\Uuid\UuidInterface
     {
         return $this->id;
     }
 }
```

<br><br>

### `ChangeGetUuidMethodCallToGetIdRector`

- class: [`Rector\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector`](/rules/doctrine/src/Rector/MethodCall/ChangeGetUuidMethodCallToGetIdRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/MethodCall/ChangeGetUuidMethodCallToGetIdRector/Fixture)

Change `getUuid()` method call to `getId()`

```diff
 use Doctrine\ORM\Mapping as ORM;
 use Ramsey\Uuid\Uuid;
 use Ramsey\Uuid\UuidInterface;

 class SomeClass
 {
     public function run()
     {
         $buildingFirst = new Building();

-        return $buildingFirst->getUuid()->toString();
+        return $buildingFirst->getId()->toString();
     }
 }

 /**
  * @ORM\Entity
  */
 class UuidEntity
 {
     private $uuid;
     public function getUuid(): UuidInterface
     {
         return $this->uuid;
     }
 }
```

<br><br>

### `ChangeIdenticalUuidToEqualsMethodCallRector`

- class: [`Rector\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector`](/rules/doctrine/src/Rector/Identical/ChangeIdenticalUuidToEqualsMethodCallRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Identical/ChangeIdenticalUuidToEqualsMethodCallRector/Fixture)

Change `$uuid` === 1 to `$uuid->equals(\Ramsey\Uuid\Uuid::fromString(1))`

```diff
 class SomeClass
 {
     public function match($checkedId): int
     {
         $building = new Building();

-        return $building->getId() === $checkedId;
+        return $building->getId()->equals(\Ramsey\Uuid\Uuid::fromString($checkedId));
     }
 }
```

<br><br>

### `ChangeReturnTypeOfClassMethodWithGetIdRector`

- class: [`Rector\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector`](/rules/doctrine/src/Rector/ClassMethod/ChangeReturnTypeOfClassMethodWithGetIdRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/ClassMethod/ChangeReturnTypeOfClassMethodWithGetIdRector/Fixture)

Change `getUuid()` method call to `getId()`

```diff
 class SomeClass
 {
-    public function getBuildingId(): int
+    public function getBuildingId(): \Ramsey\Uuid\UuidInterface
     {
         $building = new Building();

         return $building->getId();
     }
 }
```

<br><br>

### `ChangeSetIdToUuidValueRector`

- class: [`Rector\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector`](/rules/doctrine/src/Rector/MethodCall/ChangeSetIdToUuidValueRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/MethodCall/ChangeSetIdToUuidValueRector/Fixture)

Change set id to uuid values

```diff
 use Doctrine\ORM\Mapping as ORM;
 use Ramsey\Uuid\Uuid;

 class SomeClass
 {
     public function run()
     {
         $buildingFirst = new Building();
-        $buildingFirst->setId(1);
-        $buildingFirst->setUuid(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
+        $buildingFirst->setId(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
     }
 }

 /**
  * @ORM\Entity
  */
 class Building
 {
 }
```

<br><br>

### `ChangeSetIdTypeToUuidRector`

- class: [`Rector\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector`](/rules/doctrine/src/Rector/ClassMethod/ChangeSetIdTypeToUuidRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/ClassMethod/ChangeSetIdTypeToUuidRector/Fixture)

Change param type of `setId()` to uuid interface

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SetId
 {
     private $id;

-    public function setId(int $uuid): int
+    public function setId(\Ramsey\Uuid\UuidInterface $uuid): int
     {
         return $this->id = $uuid;
     }
 }
```

<br><br>

### `EntityAliasToClassConstantReferenceRector`

- class: [`Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector`](/rules/doctrine/src/Rector/MethodCall/EntityAliasToClassConstantReferenceRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/MethodCall/EntityAliasToClassConstantReferenceRector/Fixture)

Replaces doctrine alias with class.

```php
<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(EntityAliasToClassConstantReferenceRector::class)
        ->call(
            'configure',
            [[
                EntityAliasToClassConstantReferenceRector::ALIASES_TO_NAMESPACES => [
                    App::class => 'App\Entity',
                ],
            ]]
        );
};
```

↓

```diff
 $entityManager = new Doctrine\ORM\EntityManager();
-$entityManager->getRepository("AppBundle:Post");
+$entityManager->getRepository(\App\Entity\Post::class);
```

<br><br>

### `ManagerRegistryGetManagerToEntityManagerRector`

- class: [`Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector`](/rules/doctrine/src/Rector/Class_/ManagerRegistryGetManagerToEntityManagerRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Class_/ManagerRegistryGetManagerToEntityManagerRector/Fixture)

Changes ManagerRegistry intermediate calls directly to EntityManager calls

```diff
-use Doctrine\Common\Persistence\ManagerRegistry;
+use Doctrine\ORM\EntityManagerInterface;

 class CustomRepository
 {
     /**
-     * @var ManagerRegistry
+     * @var EntityManagerInterface
      */
-    private $managerRegistry;
+    private $entityManager;

-    public function __construct(ManagerRegistry $managerRegistry)
+    public function __construct(EntityManagerInterface $entityManager)
     {
-        $this->managerRegistry = $managerRegistry;
+        $this->entityManager = $entityManager;
     }

     public function run()
     {
-        $entityManager = $this->managerRegistry->getManager();
-        $someRepository = $entityManager->getRepository('Some');
+        $someRepository = $this->entityManager->getRepository('Some');
     }
 }
```

<br><br>

### `RemoveRepositoryFromEntityAnnotationRector`

- class: [`Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector`](/rules/doctrine/src/Rector/Class_/RemoveRepositoryFromEntityAnnotationRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Class_/RemoveRepositoryFromEntityAnnotationRector/Fixture)

Removes repository class from @Entity annotation

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
- * @ORM\Entity(repositoryClass="ProductRepository")
+ * @ORM\Entity
  */
 class Product
 {
 }
```

<br><br>

### `RemoveTemporaryUuidColumnPropertyRector`

- class: [`Rector\Doctrine\Rector\Property\RemoveTemporaryUuidColumnPropertyRector`](/rules/doctrine/src/Rector/Property/RemoveTemporaryUuidColumnPropertyRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Property/RemoveTemporaryUuidColumnPropertyRector/Fixture)

Remove temporary `$uuid` property

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class Column
 {
     /**
      * @ORM\Column
      */
     public $id;
-
-    /**
-     * @ORM\Column
-     */
-    public $uuid;
 }
```

<br><br>

### `RemoveTemporaryUuidRelationPropertyRector`

- class: [`Rector\Doctrine\Rector\Property\RemoveTemporaryUuidRelationPropertyRector`](/rules/doctrine/src/Rector/Property/RemoveTemporaryUuidRelationPropertyRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/Property/RemoveTemporaryUuidRelationPropertyRector/Fixture)

Remove temporary *Uuid relation properties

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class Column
 {
     /**
      * @ORM\ManyToMany(targetEntity="Phonenumber")
      */
     private $apple;
-
-    /**
-     * @ORM\ManyToMany(targetEntity="Phonenumber")
-     */
-    private $appleUuid;
 }
```

<br><br>

### `ServiceEntityRepositoryConstructorToDependencyInjectionWithRepositoryPropertyRector`

- class: [`Rector\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryConstructorToDependencyInjectionWithRepositoryPropertyRector`](/rules/doctrine/src/Rector/ClassMethod/ServiceEntityRepositoryConstructorToDependencyInjectionWithRepositoryPropertyRector.php)
- [test fixtures](/rules/doctrine/tests/Rector/ClassMethod/ServiceEntityRepositoryConstructorToDependencyInjectionWithRepositoryPropertyRector/Fixture)

Change ServiceEntityRepository to dependency injection, with repository property

```diff
 use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
 use Doctrine\Persistence\ManagerRegistry;

 final class ProjectRepository extends ServiceEntityRepository
 {
-    public function __construct(ManagerRegistry $registry)
+    /**
+     * @var \Doctrine\ORM\EntityManagerInterface
+     */
+    private $entityManager;
+
+    /**
+     * @var \Doctrine\ORM\EntityRepository<Project>
+     */
+    private $repository;
+
+    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
     {
-        parent::__construct($registry, Project::class);
+        $this->repository = $entityManager->getRepository(Project::class);
+        $this->entityManager = $entityManager;
     }
 }
```

<br><br>

## DoctrineCodeQuality

### `ChangeBigIntEntityPropertyToIntTypeRector`

- class: [`Rector\DoctrineCodeQuality\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector`](/rules/doctrine-code-quality/src/Rector/Property/ChangeBigIntEntityPropertyToIntTypeRector.php)
- [test fixtures](/rules/doctrine-code-quality/tests/Rector/Property/ChangeBigIntEntityPropertyToIntTypeRector/Fixture)

Change database type "bigint" for @var/type declaration to string

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class SomeEntity
 {
     /**
-     * @var int|null
+     * @var string|null
      * @ORM\Column(type="bigint", nullable=true)
      */
     private $bigNumber;
 }
```

<br><br>

### `ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector`

- class: [`Rector\DoctrineCodeQuality\Rector\MethodCall\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector`](/rules/doctrine-code-quality/src/Rector/MethodCall/ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector.php)
- [test fixtures](/rules/doctrine-code-quality/tests/Rector/MethodCall/ChangeQuerySetParametersMethodParameterFromArrayToArrayCollection/Fixture)

Change array to ArrayCollection in setParameters method of query builder

```diff
+use Doctrine\Common\Collections\ArrayCollection;
 use Doctrine\ORM\EntityRepository;
+use Doctrine\ORM\Query\Parameter;

 class SomeRepository extends EntityRepository
 {
     public function getSomething()
     {
         return $this
             ->createQueryBuilder('sm')
             ->select('sm')
             ->where('sm.foo = :bar')
-            ->setParameters([
-                'bar' => 'baz'
-            ])
+            ->setParameters(new ArrayCollection([
+                new  Parameter('bar', 'baz'),
+            ]))
             ->getQuery()
             ->getResult()
         ;
     }
 }
```

<br><br>

### `CorrectDefaultTypesOnEntityPropertyRector`

- class: [`Rector\DoctrineCodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector`](/rules/doctrine-code-quality/src/Rector/Property/CorrectDefaultTypesOnEntityPropertyRector.php)
- [test fixtures](/rules/doctrine-code-quality/tests/Rector/Property/CorrectDefaultTypesOnEntityPropertyRector/Fixture)

Change default value types to match Doctrine annotation type

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class User
 {
     /**
      * @ORM\Column(name="is_old", type="boolean")
      */
-    private $isOld = '0';
+    private $isOld = false;
 }
```

<br><br>

### `InitializeDefaultEntityCollectionRector`

- class: [`Rector\DoctrineCodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector`](/rules/doctrine-code-quality/src/Rector/Class_/InitializeDefaultEntityCollectionRector.php)
- [test fixtures](/rules/doctrine-code-quality/tests/Rector/Class_/InitializeDefaultEntityCollectionRector/Fixture)

Initialize collection property in Entity constructor

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SomeClass
 {
     /**
      * @ORM\OneToMany(targetEntity="MarketingEvent")
      */
     private $marketingEvents = [];
+
+    public function __construct()
+    {
+        $this->marketingEvents = new ArrayCollection();
+    }
 }
```

<br><br>

### `MakeEntityDateTimePropertyDateTimeInterfaceRector`

- class: [`Rector\DoctrineCodeQuality\Rector\ClassMethod\MakeEntityDateTimePropertyDateTimeInterfaceRector`](/rules/doctrine-code-quality/src/Rector/ClassMethod/MakeEntityDateTimePropertyDateTimeInterfaceRector.php)
- [test fixtures](/rules/doctrine-code-quality/tests/Rector/ClassMethod/MakeEntityDateTimePropertyDateTimeInterfaceRector/Fixture)

Make maker bundle generate DateTime property accept DateTimeInterface too

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class User
 {
     /**
-     * @var DateTime|null
+     * @var DateTimeInterface|null
      */
     private $bornAt;

     public function setBornAt(DateTimeInterface $bornAt)
     {
         $this->bornAt = $bornAt;
     }
 }
```

<br><br>

### `MakeEntitySetterNullabilityInSyncWithPropertyRector`

- class: [`Rector\DoctrineCodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector`](/rules/doctrine-code-quality/src/Rector/ClassMethod/MakeEntitySetterNullabilityInSyncWithPropertyRector.php)
- [test fixtures](/rules/doctrine-code-quality/tests/Rector/ClassMethod/MakeEntitySetterNullabilityInSyncWithPropertyRector/Fixture)

Make nullability in setter class method with respect to property

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class Product
 {
     /**
      * @ORM\ManyToOne(targetEntity="AnotherEntity")
      */
     private $anotherEntity;

-    public function setAnotherEntity(?AnotherEntity $anotherEntity)
+    public function setAnotherEntity(AnotherEntity $anotherEntity)
     {
         $this->anotherEntity = $anotherEntity;
     }
 }
```

<br><br>

### `MoveCurrentDateTimeDefaultInEntityToConstructorRector`

- class: [`Rector\DoctrineCodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector`](/rules/doctrine-code-quality/src/Rector/Class_/MoveCurrentDateTimeDefaultInEntityToConstructorRector.php)
- [test fixtures](/rules/doctrine-code-quality/tests/Rector/Property/MoveCurrentDateTimeDefaultInEntityToConstructorRector/Fixture)

Move default value for entity property to constructor, the safest place

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class User
 {
     /**
      * @var DateTimeInterface
      *
-     * @ORM\Column(type="datetime", nullable=false, options={"default"="now()"})
+     * @ORM\Column(type="datetime", nullable=false)
      */
-    private $when = 'now()';
+    private $when;
+
+    public function __construct()
+    {
+        $this->when = new \DateTime();
+    }
 }
```

<br><br>

### `MoveRepositoryFromParentToConstructorRector`

- class: [`Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector`](/rules/doctrine-code-quality/src/Rector/Class_/MoveRepositoryFromParentToConstructorRector.php)

Turns parent EntityRepository class to constructor dependency

```diff
 namespace App\Repository;

+use App\Entity\Post;
 use Doctrine\ORM\EntityRepository;

-final class PostRepository extends EntityRepository
+final class PostRepository
 {
+    /**
+     * @var \Doctrine\ORM\EntityRepository
+     */
+    private $repository;
+    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
+    {
+        $this->repository = $entityManager->getRepository(\App\Entity\Post::class);
+    }
 }
```

<br><br>

## DoctrineGedmoToKnplabs

### `BlameableBehaviorRector`

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\BlameableBehaviorRector`](/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/BlameableBehaviorRector.php)
- [test fixtures](/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/BlameableBehaviorRector/Fixture)

Change Blameable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Mapping\Annotation as Gedmo;
 use Doctrine\ORM\Mapping as ORM;
+use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
+use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;

 /**
  * @ORM\Entity
  */
-class SomeClass
+class SomeClass implements BlameableInterface
 {
-    /**
-     * @Gedmo\Blameable(on="create")
-     */
-    private $createdBy;
-
-    /**
-     * @Gedmo\Blameable(on="update")
-     */
-    private $updatedBy;
-
-    /**
-     * @Gedmo\Blameable(on="change", field={"title", "body"})
-     */
-    private $contentChangedBy;
-
-    public function getCreatedBy()
-    {
-        return $this->createdBy;
-    }
-
-    public function getUpdatedBy()
-    {
-        return $this->updatedBy;
-    }
-
-    public function getContentChangedBy()
-    {
-        return $this->contentChangedBy;
-    }
+    use BlameableTrait;
 }
```

<br><br>

### `LoggableBehaviorRector`

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\LoggableBehaviorRector`](/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/LoggableBehaviorRector.php)
- [test fixtures](/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/LoggableBehaviorRector/Fixture)

Change Loggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Mapping\Annotation as Gedmo;
 use Doctrine\ORM\Mapping as ORM;
+use Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;
+use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;

 /**
  * @ORM\Entity
- * @Gedmo\Loggable
  */
-class SomeClass
+class SomeClass implements LoggableInterface
 {
+    use LoggableTrait;
+
     /**
-     * @Gedmo\Versioned
      * @ORM\Column(name="title", type="string", length=8)
      */
     private $title;
 }
```

<br><br>

### `SluggableBehaviorRector`

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\SluggableBehaviorRector`](/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/SluggableBehaviorRector.php)
- [test fixtures](/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/SluggableBehaviorRector/Fixture)

Change Sluggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
 use Gedmo\Mapping\Annotation as Gedmo;
+use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
+use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;

-class SomeClass
+class SomeClass implements SluggableInterface
 {
+    use SluggableTrait;
+
     /**
-     * @Gedmo\Slug(fields={"name"})
+     * @return string[]
      */
-    private $slug;
-
-    public function getSlug(): ?string
+    public function getSluggableFields(): array
     {
-        return $this->slug;
-    }
-
-    public function setSlug(?string $slug): void
-    {
-        $this->slug = $slug;
+        return ['name'];
     }
 }
```

<br><br>

### `SoftDeletableBehaviorRector`

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\SoftDeletableBehaviorRector`](/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/SoftDeletableBehaviorRector.php)
- [test fixtures](/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/SoftDeletableBehaviorRector/Fixture)

Change SoftDeletable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Mapping\Annotation as Gedmo;
+use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;
+use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait;

-/**
- * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
- */
-class SomeClass
+class SomeClass implements SoftDeletableInterface
 {
-    /**
-     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
-     */
-    private $deletedAt;
-
-    public function getDeletedAt()
-    {
-        return $this->deletedAt;
-    }
-
-    public function setDeletedAt($deletedAt)
-    {
-        $this->deletedAt = $deletedAt;
-    }
+    use SoftDeletableTrait;
 }
```

<br><br>

### `TimestampableBehaviorRector`

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\TimestampableBehaviorRector`](/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/TimestampableBehaviorRector.php)
- [test fixtures](/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/TimestampableBehaviorRector/Fixture)

Change Timestampable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Timestampable\Traits\TimestampableEntity;
+use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
+use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;

-class SomeClass
+class SomeClass implements TimestampableInterface
 {
-    use TimestampableEntity;
+    use TimestampableTrait;
 }
```

<br><br>

### `TranslationBehaviorRector`

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\TranslationBehaviorRector`](/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/TranslationBehaviorRector.php)
- [test fixtures](/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/TranslationBehaviorRector/Fixture)

Change Translation from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Mapping\Annotation as Gedmo;
-use Doctrine\ORM\Mapping as ORM;
-use Gedmo\Translatable\Translatable;
+use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
+use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

-/**
- * @ORM\Table
- */
-class Article implements Translatable
+class SomeClass implements TranslatableInterface
 {
+    use TranslatableTrait;
+}
+
+
+use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
+use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;
+
+class SomeClassTranslation implements TranslationInterface
+{
+    use TranslationTrait;
+
     /**
-     * @Gedmo\Translatable
      * @ORM\Column(length=128)
      */
     private $title;

     /**
-     * @Gedmo\Translatable
      * @ORM\Column(type="text")
      */
     private $content;
-
-    /**
-     * @Gedmo\Locale
-     * Used locale to override Translation listener`s locale
-     * this is not a mapped field of entity metadata, just a simple property
-     * and it is not necessary because globally locale can be set in listener
-     */
-    private $locale;
-
-    public function setTitle($title)
-    {
-        $this->title = $title;
-    }
-
-    public function getTitle()
-    {
-        return $this->title;
-    }
-
-    public function setContent($content)
-    {
-        $this->content = $content;
-    }
-
-    public function getContent()
-    {
-        return $this->content;
-    }
-
-    public function setTranslatableLocale($locale)
-    {
-        $this->locale = $locale;
-    }
 }
```

<br><br>

### `TreeBehaviorRector`

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\TreeBehaviorRector`](/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/TreeBehaviorRector.php)
- [test fixtures](/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/TreeBehaviorRector/Fixture)

Change Tree from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Doctrine\Common\Collections\Collection;
-use Gedmo\Mapping\Annotation as Gedmo;
+use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
+use Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

-/**
- * @Gedmo\Tree(type="nested")
- */
-class SomeClass
+class SomeClass implements TreeNodeInterface
 {
-    /**
-     * @Gedmo\TreeLeft
-     * @ORM\Column(name="lft", type="integer")
-     * @var int
-     */
-    private $lft;
-
-    /**
-     * @Gedmo\TreeRight
-     * @ORM\Column(name="rgt", type="integer")
-     * @var int
-     */
-    private $rgt;
-
-    /**
-     * @Gedmo\TreeLevel
-     * @ORM\Column(name="lvl", type="integer")
-     * @var int
-     */
-    private $lvl;
-
-    /**
-     * @Gedmo\TreeRoot
-     * @ORM\ManyToOne(targetEntity="Category")
-     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
-     * @var Category
-     */
-    private $root;
-
-    /**
-     * @Gedmo\TreeParent
-     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
-     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
-     * @var Category
-     */
-    private $parent;
-
-    /**
-     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
-     * @var Category[]|Collection
-     */
-    private $children;
-
-    public function getRoot(): self
-    {
-        return $this->root;
-    }
-
-    public function setParent(self $category): void
-    {
-        $this->parent = $category;
-    }
-
-    public function getParent(): self
-    {
-        return $this->parent;
-    }
+    use TreeNodeTrait;
 }
```

<br><br>

## DowngradePhp71

### `DowngradeNullableTypeParamDeclarationRector`

- class: [`Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector`](/rules/downgrade-php71/src/Rector/FunctionLike/DowngradeNullableTypeParamDeclarationRector.php)
- [test fixtures](/rules/downgrade-php71/tests/Rector/FunctionLike/DowngradeNullableTypeParamDeclarationRector/Fixture)

Remove the nullable type params, add @param tags instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeNullableTypeParamDeclarationRector::class)
        ->call('configure', [[
            DowngradeNullableTypeParamDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function run(?string $input)
+    /**
+     * @param string|null $input
+     */
+    public function run($input)
     {
         // do something
     }
 }
```

<br><br>

### `DowngradeNullableTypeReturnDeclarationRector`

- class: [`Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeReturnDeclarationRector`](/rules/downgrade-php71/src/Rector/FunctionLike/DowngradeNullableTypeReturnDeclarationRector.php)
- [test fixtures](/rules/downgrade-php71/tests/Rector/FunctionLike/DowngradeNullableTypeReturnDeclarationRector/Fixture)

Remove returning nullable types, add a @return tag instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeReturnDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeNullableTypeReturnDeclarationRector::class)
        ->call('configure', [[
            DowngradeNullableTypeReturnDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function getResponseOrNothing(bool $flag): ?string
+    /**
+     * @return string|null
+     */
+    public function getResponseOrNothing(bool $flag)
     {
         if ($flag) {
             return 'Hello world';
         }
         return null;
     }
 }
```

<br><br>

### `DowngradeVoidTypeReturnDeclarationRector`

- class: [`Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector`](/rules/downgrade-php71/src/Rector/FunctionLike/DowngradeVoidTypeReturnDeclarationRector.php)
- [test fixtures](/rules/downgrade-php71/tests/Rector/FunctionLike/DowngradeVoidTypeReturnDeclarationRector/Fixture)

Remove the 'void' function type, add a @return tag instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeVoidTypeReturnDeclarationRector::class)
        ->call('configure', [[
            DowngradeVoidTypeReturnDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function run(): void
+    /**
+     * @return void
+     */
+    public function run()
     {
         // do something
     }
 }
```

<br><br>

## DowngradePhp72

### `DowngradeParamObjectTypeDeclarationRector`

- class: [`Rector\DowngradePhp72\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector`](/rules/downgrade-php72/src/Rector/FunctionLike/DowngradeParamObjectTypeDeclarationRector.php)
- [test fixtures](/rules/downgrade-php72/tests/Rector/FunctionLike/DowngradeParamObjectTypeDeclarationRector/Fixture)

Remove the 'object' param type, add a @param tag instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeParamObjectTypeDeclarationRector::class)
        ->call('configure', [[
            DowngradeParamObjectTypeDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function someFunction(object $someObject)
+    /**
+     * @param object $someObject
+     */
+    public function someFunction($someObject)
     {
     }
 }
```

<br><br>

### `DowngradeReturnObjectTypeDeclarationRector`

- class: [`Rector\DowngradePhp72\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector`](/rules/downgrade-php72/src/Rector/FunctionLike/DowngradeReturnObjectTypeDeclarationRector.php)
- [test fixtures](/rules/downgrade-php72/tests/Rector/FunctionLike/DowngradeReturnObjectTypeDeclarationRector/Fixture)

Remove the 'object' function type, add a @return tag instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeReturnObjectTypeDeclarationRector::class)
        ->call('configure', [[
            DowngradeReturnObjectTypeDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function getSomeObject(): object
+    /**
+     * @return object
+     */
+    public function getSomeObject()
     {
         return new SomeObject();
     }
 }
```

<br><br>

## DowngradePhp74

### `ArrowFunctionToAnonymousFunctionRector`

- class: [`Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector`](/rules/downgrade-php74/src/Rector/ArrowFunction/ArrowFunctionToAnonymousFunctionRector.php)
- [test fixtures](/rules/downgrade-php74/tests/Rector/ArrowFunction/ArrowFunctionToAnonymousFunctionRector/Fixture)

Replace arrow functions with anonymous functions

```diff
 class SomeClass
 {
     public function run()
     {
         $delimiter = ",";
-        $callable = fn($matches) => $delimiter . strtolower($matches[1]);
+        $callable = function ($matches) use ($delimiter) {
+            return $delimiter . strtolower($matches[1]);
+        };
     }
 }
```

<br><br>

### `DowngradeNullCoalescingOperatorRector`

- class: [`Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector`](/rules/downgrade-php74/src/Rector/Coalesce/DowngradeNullCoalescingOperatorRector.php)
- [test fixtures](/rules/downgrade-php74/tests/Rector/Coalesce/DowngradeNullCoalescingOperatorRector/Fixture)

Remove null coalescing operator ??=

```diff
 $array = [];
-$array['user_id'] ??= 'value';
+$array['user_id'] = $array['user_id'] ?? 'value';
```

<br><br>

### `DowngradeTypedPropertyRector`

- class: [`Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector`](/rules/downgrade-php74/src/Rector/Property/DowngradeTypedPropertyRector.php)
- [test fixtures](/rules/downgrade-php74/tests/Rector/Property/DowngradeTypedPropertyRector/Fixture)

Changes property type definition from type definitions to `@var` annotations.

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeTypedPropertyRector::class)
        ->call('configure', [[
            DowngradeTypedPropertyRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 class SomeClass
 {
-    private string $property;
+    /**
+    * @var string
+    */
+    private $property;
 }
```

<br><br>

## DowngradePhp80

### `DowngradeParamMixedTypeDeclarationRector`

- class: [`Rector\DowngradePhp80\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector`](/rules/downgrade-php80/src/Rector/FunctionLike/DowngradeParamMixedTypeDeclarationRector.php)
- [test fixtures](/rules/downgrade-php80/tests/Rector/FunctionLike/DowngradeParamMixedTypeDeclarationRector/Fixture)

Remove the 'mixed' param type, add a @param tag instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeParamMixedTypeDeclarationRector::class)
        ->call('configure', [[
            DowngradeParamMixedTypeDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function someFunction(mixed $anything)
+    /**
+     * @param mixed $anything
+     */
+    public function someFunction($anything)
     {
     }
 }
```

<br><br>

### `DowngradeReturnMixedTypeDeclarationRector`

- class: [`Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector`](/rules/downgrade-php80/src/Rector/FunctionLike/DowngradeReturnMixedTypeDeclarationRector.php)
- [test fixtures](/rules/downgrade-php80/tests/Rector/FunctionLike/DowngradeReturnMixedTypeDeclarationRector/Fixture)

Remove the 'mixed' function type, add a @return tag instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeReturnMixedTypeDeclarationRector::class)
        ->call('configure', [[
            DowngradeReturnMixedTypeDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function getAnything(bool $flag): mixed
+    /**
+     * @return mixed
+     */
+    public function getAnything(bool $flag)
     {
         if ($flag) {
             return 1;
         }
         return 'Hello world'
     }
 }
```

<br><br>

### `DowngradeReturnStaticTypeDeclarationRector`

- class: [`Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector`](/rules/downgrade-php80/src/Rector/FunctionLike/DowngradeReturnStaticTypeDeclarationRector.php)
- [test fixtures](/rules/downgrade-php80/tests/Rector/FunctionLike/DowngradeReturnStaticTypeDeclarationRector/Fixture)

Remove the 'static' function type, add a @return tag instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeReturnStaticTypeDeclarationRector::class)
        ->call('configure', [[
            DowngradeReturnStaticTypeDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function getStatic(): static
+    /**
+     * @return static
+     */
+    public function getStatic()
     {
         return new static();
     }
 }
```

<br><br>

### `DowngradeUnionTypeParamDeclarationRector`

- class: [`Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector`](/rules/downgrade-php80/src/Rector/FunctionLike/DowngradeUnionTypeParamDeclarationRector.php)
- [test fixtures](/rules/downgrade-php80/tests/Rector/FunctionLike/DowngradeUnionTypeParamDeclarationRector/Fixture)

Remove the union type params, add @param tags instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeUnionTypeParamDeclarationRector::class)
        ->call('configure', [[
            DowngradeUnionTypeParamDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function echoInput(string|int $input)
+    /**
+     * @param string|int $input
+     */
+    public function echoInput($input)
     {
         echo $input;
     }
 }
```

<br><br>

### `DowngradeUnionTypeReturnDeclarationRector`

- class: [`Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector`](/rules/downgrade-php80/src/Rector/FunctionLike/DowngradeUnionTypeReturnDeclarationRector.php)
- [test fixtures](/rules/downgrade-php80/tests/Rector/FunctionLike/DowngradeUnionTypeReturnDeclarationRector/Fixture)

Remove returning union types, add a @return tag instead

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeUnionTypeReturnDeclarationRector::class)
        ->call('configure', [[
            DowngradeUnionTypeReturnDeclarationRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 <?php

 class SomeClass
 {
-    public function getSomeObject(bool $flag): string|int
+    /**
+     * @return string|int
+     */
+    public function getSomeObject(bool $flag)
     {
         if ($flag) {
             return 1;
         }
         return 'Hello world';
     }
 }
```

<br><br>

### `DowngradeUnionTypeTypedPropertyRector`

- class: [`Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector`](/rules/downgrade-php80/src/Rector/Property/DowngradeUnionTypeTypedPropertyRector.php)
- [test fixtures](/rules/downgrade-php80/tests/Rector/Property/DowngradeUnionTypeTypedPropertyRector/Fixture)

Removes union type property type definition, adding `@var` annotations instead.

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeUnionTypeTypedPropertyRector::class)
        ->call('configure', [[
            DowngradeUnionTypeTypedPropertyRector::ADD_DOC_BLOCK => true
        ]]);
};
```

↓

```diff
 class SomeClass
 {
-    private string|int $property;
+    /**
+    * @var string|int
+    */
+    private $property;
 }
```

<br><br>

## DynamicTypeAnalysis

### `AddArgumentTypeWithProbeDataRector`

- class: [`Rector\DynamicTypeAnalysis\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector`](/packages/dynamic-type-analysis/src/Rector/ClassMethod/AddArgumentTypeWithProbeDataRector.php)
- [test fixtures](/packages/dynamic-type-analysis/tests/Rector/ClassMethod/AddArgumentTypeWithProbeDataRector/Fixture)

Add argument type based on probed data

```diff
 class SomeClass
 {
-    public function run($arg)
+    public function run(string $arg)
     {
     }
 }
```

<br><br>

### `DecorateMethodWithArgumentTypeProbeRector`

- class: [`Rector\DynamicTypeAnalysis\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector`](/packages/dynamic-type-analysis/src/Rector/ClassMethod/DecorateMethodWithArgumentTypeProbeRector.php)
- [test fixtures](/packages/dynamic-type-analysis/tests/Rector/ClassMethod/DecorateMethodWithArgumentTypeProbeRector/Fixture)

Add probe that records argument types to `each` method

```diff
 class SomeClass
 {
     public function run($arg)
     {
+        \Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe::recordArgumentType($arg, __METHOD__, 0);
     }
 }
```

<br><br>

### `RemoveArgumentTypeProbeRector`

- class: [`Rector\DynamicTypeAnalysis\Rector\StaticCall\RemoveArgumentTypeProbeRector`](/packages/dynamic-type-analysis/src/Rector/StaticCall/RemoveArgumentTypeProbeRector.php)
- [test fixtures](/packages/dynamic-type-analysis/tests/Rector/StaticCall/RemoveArgumentTypeProbeRector/Fixture)

Clean up probe that records argument types

```diff
-use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;
-
 class SomeClass
 {
     public function run($arg)
     {
-        TypeStaticProbe::recordArgumentType($arg, __METHOD__, 0);
     }
 }
```

<br><br>

## FileSystemRector

### `RemoveProjectFileRector`

- class: [`Rector\FileSystemRector\Rector\Removing\RemoveProjectFileRector`](/packages/file-system-rector/src/Rector/Removing/RemoveProjectFileRector.php)

Remove file relative to project directory

```php
<?php

declare(strict_types=1);

use Rector\FileSystemRector\Rector\Removing\RemoveProjectFileRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveProjectFileRector::class)
        ->call('configure', [[
            RemoveProjectFileRector::FILE_PATHS_TO_REMOVE => ['someFile/ToBeRemoved.txt'],
        ]]);
};
```

↓

```diff
-// someFile/ToBeRemoved.txt
```

<br><br>

## Generic

### `ActionInjectionToConstructorInjectionRector`

- class: [`Rector\Generic\Rector\Class_\ActionInjectionToConstructorInjectionRector`](/rules/generic/src/Rector/Class_/ActionInjectionToConstructorInjectionRector.php)
- [test fixtures](/rules/generic/tests/Rector/Class_/ActionInjectionToConstructorInjectionRector/Fixture)

Turns action injection in Controllers to constructor injection

```diff
 final class SomeController
 {
-    public function default(ProductRepository $productRepository)
+    /**
+     * @var ProductRepository
+     */
+    private $productRepository;
+    public function __construct(ProductRepository $productRepository)
     {
-        $products = $productRepository->fetchAll();
+        $this->productRepository = $productRepository;
+    }
+
+    public function default()
+    {
+        $products = $this->productRepository->fetchAll();
     }
 }
```

<br><br>

### `AddInterfaceByTraitRector`

- class: [`Rector\Generic\Rector\Class_\AddInterfaceByTraitRector`](/rules/generic/src/Rector/Class_/AddInterfaceByTraitRector.php)
- [test fixtures](/rules/generic/tests/Rector/Class_/AddInterfaceByTraitRector/Fixture)

Add interface by used trait

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\AddInterfaceByTraitRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddInterfaceByTraitRector::class)
        ->call('configure', [[
            AddInterfaceByTraitRector::INTERFACE_BY_TRAIT => [
                'SomeTrait' => SomeInterface::class,
            ],
        ]]);
};
```

↓

```diff
-class SomeClass
+class SomeClass implements SomeInterface
 {
     use SomeTrait;
 }
```

<br><br>

### `AddMethodParentCallRector`

- class: [`Rector\Generic\Rector\ClassMethod\AddMethodParentCallRector`](/rules/generic/src/Rector/ClassMethod/AddMethodParentCallRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassMethod/AddMethodParentCallRector/Fixture)

Add method parent call, in case new parent method is added

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddMethodParentCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddMethodParentCallRector::class)
        ->call(
            'configure',
            [[
                AddMethodParentCallRector::METHODS_BY_PARENT_TYPES => [
                    'ParentClassWithNewConstructor' => '__construct',
                ],
            ]]
        );
};
```

↓

```diff
 class SunshineCommand extends ParentClassWithNewConstructor
 {
     public function __construct()
     {
         $value = 5;
+
+        parent::__construct();
     }
 }
```

<br><br>

### `AddPropertyByParentRector`

- class: [`Rector\Generic\Rector\Class_\AddPropertyByParentRector`](/rules/generic/src/Rector/Class_/AddPropertyByParentRector.php)
- [test fixtures](/rules/generic/tests/Rector/Class_/AddPropertyByParentRector/Fixture)

Add dependency via constructor by parent class type

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\AddPropertyByParentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddPropertyByParentRector::class)
        ->call(
            'configure',
            [[
                AddPropertyByParentRector::PARENT_DEPENDENCIES => [
                    'SomeParentClass' => ['SomeDependency'],
                ],
            ]]
        );
};
```

↓

```diff
 final class SomeClass extends SomeParentClass
 {
+    /**
+     * @var SomeDependency
+     */
+    private $someDependency;
+
+    public function __construct(SomeDependency $someDependency)
+    {
+        $this->someDependency = $someDependency;
+    }
 }
```

<br><br>

### `AddReturnTypeDeclarationRector`

- class: [`Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector`](/rules/generic/src/Rector/ClassMethod/AddReturnTypeDeclarationRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassMethod/AddReturnTypeDeclarationRector/Fixture)

Changes defined return typehint of method and class.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\ValueObject\AddReturnTypeDeclaration;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call(
            'configure',
            [[
                AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => inline_value_objects(
                                [new AddReturnTypeDeclaration('SomeClass', 'getData', 'array')]
                            )
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
-    public getData()
+    public getData(): array
     {
     }
 }
```

<br><br>

### `AnnotatedPropertyInjectToConstructorInjectionRector`

- class: [`Rector\Generic\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector`](/rules/generic/src/Rector/Property/AnnotatedPropertyInjectToConstructorInjectionRector.php)
- [test fixtures](/rules/generic/tests/Rector/Property/AnnotatedPropertyInjectToConstructorInjectionRector/Fixture)

Turns non-private properties with `@annotation` to private properties and constructor injection

```diff
 /**
  * @var SomeService
- * @inject
  */
-public $someService;
+private $someService;
+
+public function __construct(SomeService $someService)
+{
+    $this->someService = $someService;
+}
```

<br><br>

### `ArgumentAdderRector`

- class: [`Rector\Generic\Rector\ClassMethod\ArgumentAdderRector`](/rules/generic/src/Rector/ClassMethod/ArgumentAdderRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassMethod/ArgumentAdderRector/Fixture)

This Rector adds new default arguments in calls of defined methods and class types.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentAdderRector::class)
        ->call(
            'configure',
            [[
                ArgumentAdderRector::ADDED_ARGUMENTS => inline_value_objects(
                                [new ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', 'true', 'SomeType', null)]
                            )
            ]]
        );
};
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->someMethod();
+$someObject->someMethod(true);
```
```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentAdderRector::class)
        ->call(
            'configure',
            [[
                ArgumentAdderRector::ADDED_ARGUMENTS => inline_value_objects(
                                [new ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', 'true', 'SomeType', null)]
                            )
            ]]
        );
};
```

↓

```diff
 class MyCustomClass extends SomeExampleClass
 {
-    public function someMethod()
+    public function someMethod($value = true)
     {
     }
 }
```

<br><br>

### `ArgumentDefaultValueReplacerRector`

- class: [`Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector`](/rules/generic/src/Rector/ClassMethod/ArgumentDefaultValueReplacerRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassMethod/ArgumentDefaultValueReplacerRector/Fixture)

Replaces defined map of arguments in defined methods and their calls.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\ValueObject\ArgumentDefaultValueReplacer;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentDefaultValueReplacerRector::class)
        ->call(
            'configure',
            [[
                ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => inline_value_objects(
                                [new ArgumentDefaultValueReplacer(
                                    'SomeExampleClass',
                                    'someMethod',
                                    0,
                                    'SomeClass::OLD_CONSTANT',
                                    'false'
                                )]
                            )
            ]]
        );
};
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(SomeClass::OLD_CONSTANT);
+$someObject->someMethod(false);'
```

<br><br>

### `ArgumentRemoverRector`

- class: [`Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector`](/rules/generic/src/Rector/ClassMethod/ArgumentRemoverRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassMethod/ArgumentRemoverRector/Fixture)

Removes defined arguments in defined methods and their calls.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\ValueObject\ArgumentRemover;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentRemoverRector::class)
        ->call(
            'configure',
            [[
                ArgumentRemoverRector::REMOVED_ARGUMENTS => inline_value_objects(
                                [new ArgumentRemover('ExampleClass', 'someMethod', 0, 'true')]
                            )
            ]]
        );
};
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(true);
+$someObject->someMethod();'
```

<br><br>

### `ChangeConstantVisibilityRector`

- class: [`Rector\Generic\Rector\ClassConst\ChangeConstantVisibilityRector`](/rules/generic/src/Rector/ClassConst/ChangeConstantVisibilityRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassConst/ChangeConstantVisibilityRector/Fixture)

Change visibility of constant from parent class.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassConst\ChangeConstantVisibilityRector;
use Rector\Generic\ValueObject\ClassConstantVisibilityChange;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeConstantVisibilityRector::class)
        ->call(
            'configure',
            [[
                ChangeConstantVisibilityRector::CLASS_CONSTANT_VISIBILITY_CHANGES => inline_value_objects(
                                [new ClassConstantVisibilityChange('ParentObject', 'SOME_CONSTANT', 'protected')]
                            )
            ]]
        );
};
```

↓

```diff
 class FrameworkClass
 {
     protected const SOME_CONSTANT = 1;
 }

 class MyClass extends FrameworkClass
 {
-    public const SOME_CONSTANT = 1;
+    protected const SOME_CONSTANT = 1;
 }
```

<br><br>

### `ChangeContractMethodSingleToManyRector`

- class: [`Rector\Generic\Rector\ClassMethod\ChangeContractMethodSingleToManyRector`](/rules/generic/src/Rector/ClassMethod/ChangeContractMethodSingleToManyRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassMethod/ChangeContractMethodSingleToManyRector/Fixture)

Change method that returns single value to multiple values

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ChangeContractMethodSingleToManyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeContractMethodSingleToManyRector::class)
        ->call(
            'configure',
            [[
                ChangeContractMethodSingleToManyRector::OLD_TO_NEW_METHOD_BY_TYPE => [
                    'SomeClass' => [
                        'getNode' => 'getNodes',
                    ],
                ],
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
-    public function getNode(): string
+    /**
+     * @return string[]
+     */
+    public function getNodes(): array
     {
-        return 'Echo_';
+        return ['Echo_'];
     }
 }
```

<br><br>

### `ChangeMethodVisibilityRector`

- class: [`Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector`](/rules/generic/src/Rector/ClassMethod/ChangeMethodVisibilityRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassMethod/ChangeMethodVisibilityRector/Fixture)

Change visibility of method from parent class.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\ValueObject\ChangeMethodVisibility;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeMethodVisibilityRector::class)
        ->call(
            'configure',
            [[
                ChangeMethodVisibilityRector::METHOD_VISIBILITIES => inline_value_objects(
                                [new ChangeMethodVisibility('FrameworkClass', 'someMethod', 'protected')]
                            )
            ]]
        );
};
```

↓

```diff
 class FrameworkClass
 {
     protected someMethod()
     {
     }
 }

 class MyClass extends FrameworkClass
 {
-    public someMethod()
+    protected someMethod()
     {
     }
 }
```

<br><br>

### `ChangePropertyVisibilityRector`

- class: [`Rector\Generic\Rector\Property\ChangePropertyVisibilityRector`](/rules/generic/src/Rector/Property/ChangePropertyVisibilityRector.php)
- [test fixtures](/rules/generic/tests/Rector/Property/ChangePropertyVisibilityRector/Fixture)

Change visibility of property from parent class.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Property\ChangePropertyVisibilityRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangePropertyVisibilityRector::class)
        ->call(
            'configure',
            [[
                ChangePropertyVisibilityRector::PROPERTY_TO_VISIBILITY_BY_CLASS => [
                    'FrameworkClass' => [
                        'someProperty' => 'protected',
                    ],
                ],
            ]]
        );
};
```

↓

```diff
 class FrameworkClass
 {
     protected $someProperty;
 }

 class MyClass extends FrameworkClass
 {
-    public $someProperty;
+    protected $someProperty;
 }
```

<br><br>

### `FormerNullableArgumentToScalarTypedRector`

- class: [`Rector\Generic\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector`](/rules/generic/src/Rector/MethodCall/FormerNullableArgumentToScalarTypedRector.php)
- [test fixtures](/rules/generic/tests/Rector/MethodCall/FormerNullableArgumentToScalarTypedRector/Fixture)

Change null in argument, that is now not nullable anymore

```diff
 final class SomeClass
 {
     public function run()
     {
-        $this->setValue(null);
+        $this->setValue('');
     }

     public function setValue(string $value)
     {
     }
 }
```

<br><br>

### `FuncCallToNewRector`

- class: [`Rector\Generic\Rector\FuncCall\FuncCallToNewRector`](/rules/generic/src/Rector/FuncCall/FuncCallToNewRector.php)
- [test fixtures](/rules/generic/tests/Rector/FuncCall/FuncCallToNewRector/Fixture)

Change configured function calls to new Instance

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\FuncCallToNewRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToNewRector::class)
        ->call('configure', [[
            FuncCallToNewRector::FUNCTION_TO_NEW => [
                'collection' => ['Collection'],
            ],
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $array = collection([]);
+        $array = new \Collection([]);
     }
 }
```

<br><br>

### `FuncCallToStaticCallRector`

- class: [`Rector\Generic\Rector\FuncCall\FuncCallToStaticCallRector`](/rules/generic/src/Rector/FuncCall/FuncCallToStaticCallRector.php)
- [test fixtures](/rules/generic/tests/Rector/FuncCall/FuncCallToStaticCallRector/Fixture)

Turns defined function call to static method call.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\FuncCallToStaticCallRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToStaticCallRector::class)
        ->call(
            'configure',
            [[
                FuncCallToStaticCallRector::FUNC_CALLS_TO_STATIC_CALLS => inline_value_objects(
                                [new FuncCallToStaticCall('view', 'SomeStaticClass', 'render')]
                            )
            ]]
        );
};
```

↓

```diff
-view("...", []);
+SomeClass::render("...", []);
```

<br><br>

### `InjectAnnotationClassRector`

- class: [`Rector\Generic\Rector\Property\InjectAnnotationClassRector`](/rules/generic/src/Rector/Property/InjectAnnotationClassRector.php)
- [test fixtures](/rules/generic/tests/Rector/Property/InjectAnnotationClassRector/Fixture)

Changes properties with specified annotations class to constructor injection

```php
<?php

declare(strict_types=1);

use DI\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Inject;
use Rector\Generic\Rector\Property\InjectAnnotationClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(InjectAnnotationClassRector::class)
        ->call('configure', [[
            InjectAnnotationClassRector::ANNOTATION_CLASSES => [Inject::class, Inject::class],
        ]]);
};
```

↓

```diff
 use JMS\DiExtraBundle\Annotation as DI;

 class SomeController
 {
     /**
-     * @DI\Inject("entity.manager")
+     * @var EntityManager
      */
     private $entityManager;
+
+    public function __construct(EntityManager $entityManager)
+    {
+        $this->entityManager = entityManager;
+    }
 }
```

<br><br>

### `MergeInterfacesRector`

- class: [`Rector\Generic\Rector\Class_\MergeInterfacesRector`](/rules/generic/src/Rector/Class_/MergeInterfacesRector.php)
- [test fixtures](/rules/generic/tests/Rector/Class_/MergeInterfacesRector/Fixture)

Merges old interface to a new one, that already has its methods

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\MergeInterfacesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MergeInterfacesRector::class)
        ->call(
            'configure',
            [[
                MergeInterfacesRector::OLD_TO_NEW_INTERFACES => [
                    'SomeOldInterface' => SomeInterface::class,
                ],
            ]]
        );
};
```

↓

```diff
-class SomeClass implements SomeInterface, SomeOldInterface
+class SomeClass implements SomeInterface
 {
 }
```

<br><br>

### `MethodCallRemoverRector`

- class: [`Rector\Generic\Rector\MethodCall\MethodCallRemoverRector`](/rules/generic/src/Rector/MethodCall/MethodCallRemoverRector.php)
- [test fixtures](/rules/generic/tests/Rector/MethodCall/MethodCallRemoverRector/Fixture)

Turns "$this->something()->anything()" to "$this->anything()"

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\MethodCall\MethodCallRemoverRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallRemoverRector::class)
        ->call(
            'configure',
            [[
                MethodCallRemoverRector::METHOD_CALL_REMOVER_ARGUMENT => [
                    '$methodCallRemoverArgument' => [
                        'Car' => 'something',
                    ],
                ],
            ]]
        );
};
```

↓

```diff
 $someObject = new Car;
-$someObject->something()->anything();
+$someObject->anything();
```

<br><br>

### `MethodCallToReturnRector`

- class: [`Rector\Generic\Rector\Expression\MethodCallToReturnRector`](/rules/generic/src/Rector/Expression/MethodCallToReturnRector.php)
- [test fixtures](/rules/generic/tests/Rector/Expression/MethodCallToReturnRector/Fixture)

Wrap method call to return

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Expression\MethodCallToReturnRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToReturnRector::class)
        ->call('configure', [[
            MethodCallToReturnRector::METHOD_CALL_WRAPS => [
                'SomeClass' => ['deny'],
            ],
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $this->deny();
+        return $this->deny();
     }

     public function deny()
     {
         return 1;
     }
 }
```

<br><br>

### `NewObjectToFactoryCreateRector`

- class: [`Rector\Generic\Rector\New_\NewObjectToFactoryCreateRector`](/rules/generic/src/Rector/New_/NewObjectToFactoryCreateRector.php)
- [test fixtures](/rules/generic/tests/Rector/New_/NewObjectToFactoryCreateRector/Fixture)

Replaces creating object instances with "new" keyword with factory method.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\New_\NewObjectToFactoryCreateRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewObjectToFactoryCreateRector::class)
        ->call(
            'configure',
            [[
                NewObjectToFactoryCreateRector::OBJECT_TO_FACTORY_METHOD => [
                    'MyClass' => [
                        'class' => 'MyClassFactory',
                        'method' => 'create',
                    ],
                ],
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
+	/**
+	 * @var \MyClassFactory
+	 */
+	private $myClassFactory;
+
 	public function example() {
-		new MyClass($argument);
+		$this->myClassFactory->create($argument);
 	}
 }
```

<br><br>

### `NormalToFluentRector`

- class: [`Rector\Generic\Rector\ClassMethod\NormalToFluentRector`](/rules/generic/src/Rector/ClassMethod/NormalToFluentRector.php)
- [test fixtures](/rules/generic/tests/Rector/MethodCall/NormalToFluentRector/Fixture)

Turns fluent interface calls to classic ones.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\NormalToFluentRector;
use Rector\Generic\ValueObject\NormalToFluent;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NormalToFluentRector::class)
        ->call(
            'configure',
            [[
                NormalToFluentRector::CALLS_TO_FLUENT => inline_value_objects(
                                [new NormalToFluent('SomeClass', ['someFunction', 'otherFunction'])]
                            )
            ]]
        );
};
```

↓

```diff
 $someObject = new SomeClass();
-$someObject->someFunction();
-$someObject->otherFunction();
+$someObject->someFunction()
+    ->otherFunction();
```

<br><br>

### `ParentClassToTraitsRector`

- class: [`Rector\Generic\Rector\Class_\ParentClassToTraitsRector`](/rules/generic/src/Rector/Class_/ParentClassToTraitsRector.php)
- [test fixtures](/rules/generic/tests/Rector/Class_/ParentClassToTraitsRector/Fixture)

Replaces parent class to specific traits

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\ParentClassToTraitsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ParentClassToTraitsRector::class)
        ->call(
            'configure',
            [[
                ParentClassToTraitsRector::PARENT_CLASS_TO_TRAITS => [
                    'Nette\Object' => ['Nette\SmartObject'],
                ],
            ]]
        );
};
```

↓

```diff
-class SomeClass extends Nette\Object
+class SomeClass
 {
+    use Nette\SmartObject;
 }
```

<br><br>

### `PseudoNamespaceToNamespaceRector`

- class: [`Rector\Generic\Rector\Name\PseudoNamespaceToNamespaceRector`](/rules/generic/src/Rector/Name/PseudoNamespaceToNamespaceRector.php)
- [test fixtures](/rules/generic/tests/Rector/Name/PseudoNamespaceToNamespaceRector/Fixture)

Replaces defined Pseudo_Namespaces by Namespace\Ones.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Name\PseudoNamespaceToNamespaceRector;
use Rector\Generic\ValueObject\PseudoNamespaceToNamespace;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PseudoNamespaceToNamespaceRector::class)
        ->call(
            'configure',
            [[
                PseudoNamespaceToNamespaceRector::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES => inline_value_objects(
                                [new PseudoNamespaceToNamespace('Some_', ['Some_Class_To_Keep'])]
                            )
            ]]
        );
};
```

↓

```diff
-/** @var Some_Chicken $someService */
-$someService = new Some_Chicken;
+/** @var Some\Chicken $someService */
+$someService = new Some\Chicken;
 $someClassToKeep = new Some_Class_To_Keep;
```

<br><br>

### `RemoveAnnotationRector`

- class: [`Rector\Generic\Rector\ClassLike\RemoveAnnotationRector`](/rules/generic/src/Rector/ClassLike/RemoveAnnotationRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassLike/RemoveAnnotationRector/Fixture)

Remove annotation by names

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassLike\RemoveAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveAnnotationRector::class)
        ->call('configure', [[
            RemoveAnnotationRector::ANNOTATIONS_TO_REMOVE => ['method'],
        ]]);
};
```

↓

```diff
-/**
- * @method getName()
- */
 final class SomeClass
 {
 }
```

<br><br>

### `RemoveFuncCallArgRector`

- class: [`Rector\Generic\Rector\FuncCall\RemoveFuncCallArgRector`](/rules/generic/src/Rector/FuncCall/RemoveFuncCallArgRector.php)
- [test fixtures](/rules/generic/tests/Rector/FuncCall/RemoveFuncCallArgRector/Fixture)

Remove argument by position by function name

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Generic\ValueObject\RemoveFuncCallArg;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveFuncCallArgRector::class)
        ->call(
            'configure',
            [[
                RemoveFuncCallArgRector::REMOVED_FUNCTION_ARGUMENTS => inline_value_objects(
                                [new RemoveFuncCallArg('remove_last_arg', 1)]
                            )
            ]]
        );
};
```

↓

```diff
-remove_last_arg(1, 2);
+remove_last_arg(1);
```

<br><br>

### `RemoveIniGetSetFuncCallRector`

- class: [`Rector\Generic\Rector\FuncCall\RemoveIniGetSetFuncCallRector`](/rules/generic/src/Rector/FuncCall/RemoveIniGetSetFuncCallRector.php)
- [test fixtures](/rules/generic/tests/Rector/FuncCall/RemoveIniGetSetFuncCallRector/Fixture)

Remove `ini_get` by configuration

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\RemoveIniGetSetFuncCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveIniGetSetFuncCallRector::class)
        ->call('configure', [[
            RemoveIniGetSetFuncCallRector::KEYS_TO_REMOVE => ['y2k_compliance'],
        ]]);
};
```

↓

```diff
-ini_get('y2k_compliance');
-ini_set('y2k_compliance', 1);
```

<br><br>

### `RemoveInterfacesRector`

- class: [`Rector\Generic\Rector\Class_\RemoveInterfacesRector`](/rules/generic/src/Rector/Class_/RemoveInterfacesRector.php)
- [test fixtures](/rules/generic/tests/Rector/Class_/RemoveInterfacesRector/Fixture)

Removes interfaces usage from class.

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\RemoveInterfacesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveInterfacesRector::class)
        ->call('configure', [[
            RemoveInterfacesRector::INTERFACES_TO_REMOVE => [SomeInterface::class],
        ]]);
};
```

↓

```diff
-class SomeClass implements SomeInterface
+class SomeClass
 {
 }
```

<br><br>

### `RemoveParentRector`

- class: [`Rector\Generic\Rector\Class_\RemoveParentRector`](/rules/generic/src/Rector/Class_/RemoveParentRector.php)
- [test fixtures](/rules/generic/tests/Rector/Class_/RemoveParentRector/Fixture)

Removes extends class by name

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\RemoveParentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveParentRector::class)
        ->call('configure', [[
            RemoveParentRector::PARENT_TYPES_TO_REMOVE => ['SomeParentClass'],
        ]]);
};
```

↓

```diff
-final class SomeClass extends SomeParentClass
+final class SomeClass
 {
 }
```

<br><br>

### `RemoveTraitRector`

- class: [`Rector\Generic\Rector\Class_\RemoveTraitRector`](/rules/generic/src/Rector/Class_/RemoveTraitRector.php)
- [test fixtures](/rules/generic/tests/Rector/Class_/RemoveTraitRector/Fixture)

Remove specific traits from code

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\RemoveTraitRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveTraitRector::class)
        ->call('configure', [[
            RemoveTraitRector::TRAITS_TO_REMOVE => ['TraitNameToRemove'],
        ]]);
};
```

↓

```diff
 class SomeClass
 {
-    use SomeTrait;
 }
```

<br><br>

### `RenameClassConstantsUseToStringsRector`

- class: [`Rector\Generic\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector`](/rules/generic/src/Rector/ClassConstFetch/RenameClassConstantsUseToStringsRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassConstFetch/RenameClassConstantsUseToStringsRector/Fixture)

Replaces constant by value

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassConstantsUseToStringsRector::class)
        ->call(
            'configure',
            [[
                RenameClassConstantsUseToStringsRector::OLD_CONSTANTS_TO_NEW_VALUES_BY_TYPE => [
                    'Nette\Configurator' => [
                        'DEVELOPMENT' => 'development',
                        'PRODUCTION' => 'production',
                    ],
                ],
            ]]
        );
};
```

↓

```diff
-$value === Nette\Configurator::DEVELOPMENT
+$value === "development"
```

<br><br>

### `ReplaceParentCallByPropertyCallRector`

- class: [`Rector\Generic\Rector\MethodCall\ReplaceParentCallByPropertyCallRector`](/rules/generic/src/Rector/MethodCall/ReplaceParentCallByPropertyCallRector.php)
- [test fixtures](/rules/transform/tests/Rector/MethodCall/ReplaceParentCallByPropertyCallRector/Fixture)

Changes method calls in child of specific types to defined property method call

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplaceParentCallByPropertyCallRector::class)
        ->call(
            'configure',
            [[
                ReplaceParentCallByPropertyCallRector::PARENT_CALLS_TO_PROPERTIES => inline_value_objects(
                                [new ReplaceParentCallByPropertyCall('SomeTypeToReplace', 'someMethodCall', 'someProperty')]
                            )
            ]]
        );
};
```

↓

```diff
 final class SomeClass
 {
     public function run(SomeTypeToReplace $someTypeToReplace)
     {
-        $someTypeToReplace->someMethodCall();
+        $this->someProperty->someMethodCall();
     }
 }
```

<br><br>

### `ReplaceVariableByPropertyFetchRector`

- class: [`Rector\Generic\Rector\Variable\ReplaceVariableByPropertyFetchRector`](/rules/generic/src/Rector/Variable/ReplaceVariableByPropertyFetchRector.php)

Turns variable in controller action to property fetch, as follow up to action injection variable to property change.

```diff
 final class SomeController
 {
     /**
      * @var ProductRepository
      */
     private $productRepository;

     public function __construct(ProductRepository $productRepository)
     {
         $this->productRepository = $productRepository;
     }

     public function default()
     {
-        $products = $productRepository->fetchAll();
+        $products = $this->productRepository->fetchAll();
     }
 }
```

<br><br>

### `StringToClassConstantRector`

- class: [`Rector\Generic\Rector\String_\StringToClassConstantRector`](/rules/generic/src/Rector/String_/StringToClassConstantRector.php)
- [test fixtures](/rules/generic/tests/Rector/String_/StringToClassConstantRector/Fixture)

Changes strings to specific constants

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\String_\StringToClassConstantRector;
use Rector\Generic\ValueObject\StringToClassConstant;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringToClassConstantRector::class)
        ->call(
            'configure',
            [[
                StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => inline_value_objects(
                                [new StringToClassConstant('compiler.post_dump', 'Yet\AnotherClass', 'CONSTANT')]
                            )
            ]]
        );
};
```

↓

```diff
 final class SomeSubscriber
 {
     public static function getSubscribedEvents()
     {
-        return ['compiler.post_dump' => 'compile'];
+        return [\Yet\AnotherClass::CONSTANT => 'compile'];
     }
 }
```

<br><br>

### `SwapClassMethodArgumentsRector`

- class: [`Rector\Generic\Rector\StaticCall\SwapClassMethodArgumentsRector`](/rules/generic/src/Rector/StaticCall/SwapClassMethodArgumentsRector.php)
- [test fixtures](/rules/generic/tests/Rector/StaticCall/SwapClassMethodArgumentsRector/Fixture)

Reorder class method arguments, including their calls

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\StaticCall\SwapClassMethodArgumentsRector;
use Rector\Generic\ValueObject\SwapClassMethodArguments;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SwapClassMethodArgumentsRector::class)
        ->call(
            'configure',
            [[
                SwapClassMethodArgumentsRector::ARGUMENT_SWAPS => inline_value_objects(
                                [new SwapClassMethodArguments('SomeClass', 'run', [1, 0])]
                            )
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
-    public static function run($first, $second)
+    public static function run($second, $first)
     {
-        self::run($first, $second);
+        self::run($second, $first);
     }
 }
```

<br><br>

### `SwapFuncCallArgumentsRector`

- class: [`Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector`](/rules/generic/src/Rector/FuncCall/SwapFuncCallArgumentsRector.php)
- [test fixtures](/rules/generic/tests/Rector/FuncCall/SwapFuncCallArgumentsRector/Fixture)

Swap arguments in function calls

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector;
use Rector\Generic\ValueObject\SwapFuncCallArguments;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SwapFuncCallArgumentsRector::class)
        ->call(
            'configure',
            [[
                SwapFuncCallArgumentsRector::FUNCTION_ARGUMENT_SWAPS => inline_value_objects(
                                [new SwapFuncCallArguments('some_function', [1, 0])]
                            )
            ]]
        );
};
```

↓

```diff
 final class SomeClass
 {
     public function run($one, $two)
     {
-        return some_function($one, $two);
+        return some_function($two, $one);
     }
 }
```

<br><br>

### `WrapReturnRector`

- class: [`Rector\Generic\Rector\ClassMethod\WrapReturnRector`](/rules/generic/src/Rector/ClassMethod/WrapReturnRector.php)
- [test fixtures](/rules/generic/tests/Rector/ClassMethod/WrapReturnRector/Fixture)

Wrap return value of specific method

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\WrapReturnRector;
use Rector\Generic\ValueObject\WrapReturn;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(WrapReturnRector::class)
        ->call(
            'configure',
            [[
                WrapReturnRector::TYPE_METHOD_WRAPS => inline_value_objects(
                                [new WrapReturn('SomeClass', 'getItem', true)]
                            )
            ]]
        );
};
```

↓

```diff
 final class SomeClass
 {
     public function getItem()
     {
-        return 1;
+        return [1];
     }
 }
```

<br><br>

## JMS

### `RemoveJmsInjectParamsAnnotationRector`

- class: [`Rector\JMS\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector`](/rules/jms/src/Rector/ClassMethod/RemoveJmsInjectParamsAnnotationRector.php)
- [test fixtures](/rules/jms/tests/Rector/ClassMethod/RemoveJmsInjectParamsAnnotationRector/Fixture)

Removes `JMS\DiExtraBundle\Annotation\InjectParams` annotation

```diff
 use JMS\DiExtraBundle\Annotation as DI;

 class SomeClass
 {
-    /**
-     * @DI\InjectParams({
-     *     "subscribeService" = @DI\Inject("app.email.service.subscribe"),
-     *     "ipService" = @DI\Inject("app.util.service.ip")
-     * })
-     */
     public function __construct()
     {
     }
-}
+}
```

<br><br>

### `RemoveJmsInjectServiceAnnotationRector`

- class: [`Rector\JMS\Rector\Class_\RemoveJmsInjectServiceAnnotationRector`](/rules/jms/src/Rector/Class_/RemoveJmsInjectServiceAnnotationRector.php)
- [test fixtures](/rules/jms/tests/Rector/Class_/RemoveJmsInjectServiceAnnotationRector/Fixture)

Removes JMS\DiExtraBundle\Annotation\Services annotation

```diff
 use JMS\DiExtraBundle\Annotation as DI;

-/**
- * @DI\Service("email.web.services.subscribe_token", public=true)
- */
 class SomeClass
 {
 }
```

<br><br>

## Laravel

### `MinutesToSecondsInCacheRector`

- class: [`Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector`](/rules/laravel/src/Rector/StaticCall/MinutesToSecondsInCacheRector.php)
- [test fixtures](/rules/laravel/tests/Rector/StaticCall/MinutesToSecondsInCacheRector/Fixture)

Change minutes argument to seconds in `Illuminate\Contracts\Cache\Store` and Illuminate\Support\Facades\Cache

```diff
 class SomeClass
 {
     public function run()
     {
-        Illuminate\Support\Facades\Cache::put('key', 'value', 60);
+        Illuminate\Support\Facades\Cache::put('key', 'value', 60 * 60);
     }
 }
```

<br><br>

### `Redirect301ToPermanentRedirectRector`

- class: [`Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector`](/rules/laravel/src/Rector/StaticCall/Redirect301ToPermanentRedirectRector.php)
- [test fixtures](/rules/laravel/tests/Rector/StaticCall/Redirect301ToPermanentRedirectRector/Fixture)

Change "redirect" call with 301 to "permanentRedirect"

```diff
 class SomeClass
 {
     public function run()
     {
-        Illuminate\Routing\Route::redirect('/foo', '/bar', 301);
+        Illuminate\Routing\Route::permanentRedirect('/foo', '/bar');
     }
 }
```

<br><br>

### `RequestStaticValidateToInjectRector`

- class: [`Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector`](/rules/laravel/src/Rector/StaticCall/RequestStaticValidateToInjectRector.php)
- [test fixtures](/rules/laravel/tests/Rector/StaticCall/RequestStaticValidateToInjectRector/Fixture)

Change static `validate()` method to `$request->validate()`

```diff
 use Illuminate\Http\Request;

 class SomeClass
 {
-    public function store()
+    public function store(\Illuminate\Http\Request $request)
     {
-        $validatedData = Request::validate(['some_attribute' => 'required']);
+        $validatedData = $request->validate(['some_attribute' => 'required']);
     }
 }
```

<br><br>

## Legacy

### `AddTopIncludeRector`

- class: [`Rector\Legacy\Rector\Include_\AddTopIncludeRector`](/rules/legacy/src/Rector/Include_/AddTopIncludeRector.php)
- [test fixtures](/rules/legacy/tests/Rector/Include_/AddTopIncludeRector/Fixture)

Adds an include file at the top of matching files, except class definitions

```php
<?php

declare(strict_types=1);

use Rector\Legacy\Rector\Include_\AddTopIncludeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddTopIncludeRector::class)
        ->call(
            'configure',
            [[
                AddTopIncludeRector::AUTOLOAD_FILE_PATH => '/../autoloader.php',
                AddTopIncludeRector::PATTERNS => [
                    'pat*/*/?ame.php',
                    'somepath/?ame.php',
                ],
            ]]
        );
};
```

↓

```diff
+require_once __DIR__ . '/../autoloader.php';
+
 if (isset($_POST['csrf'])) {
     processPost($_POST);
 }
```

<br><br>

### `ChangeSingletonToServiceRector`

- class: [`Rector\Legacy\Rector\Class_\ChangeSingletonToServiceRector`](/rules/legacy/src/Rector/Class_/ChangeSingletonToServiceRector.php)
- [test fixtures](/rules/legacy/tests/Rector/Class_/ChangeSingletonToServiceRector/Fixture)

Change singleton class to normal class that can be registered as a service

```diff
 class SomeClass
 {
-    private static $instance;
-
-    private function __construct()
+    public function __construct()
     {
-    }
-
-    public static function getInstance()
-    {
-        if (null === static::$instance) {
-            static::$instance = new static();
-        }
-
-        return static::$instance;
     }
 }
```

<br><br>

### `FunctionToStaticMethodRector`

- class: [`Rector\Legacy\Rector\FileSystem\FunctionToStaticMethodRector`](/rules/legacy/src/Rector/FileSystem/FunctionToStaticMethodRector.php)

Change functions to static calls, so composer can autoload them

```diff
-function some_function()
+class SomeUtilsClass
 {
+    public static function someFunction()
+    {
+    }
 }

-some_function('lol');
+SomeUtilsClass::someFunction('lol');
```

<br><br>

### `RemoveIncludeRector`

- class: [`Rector\Legacy\Rector\Include_\RemoveIncludeRector`](/rules/legacy/src/Rector/Include_/RemoveIncludeRector.php)
- [test fixtures](/rules/legacy/tests/Rector/Include_/RemoveIncludeRector/Fixture)

Remove includes (include, include_once, require, require_once) from source

```diff
 // Comment before require
-include 'somefile.php';
+
 // Comment after require
```

<br><br>

## MagicDisclosure

### `DefluentReturnMethodCallRector`

- class: [`Rector\MagicDisclosure\Rector\Return_\DefluentReturnMethodCallRector`](/rules/magic-disclosure/src/Rector/Return_/DefluentReturnMethodCallRector.php)
- [test fixtures](/rules/magic-disclosure/tests/Rector/Return_/DefluentReturnMethodCallRector/Fixture)

Turns return of fluent, to standalone call line and return of value

```diff
 $someClass = new SomeClass();
-return $someClass->someFunction();
+$someClass->someFunction();
+return $someClass;
```

<br><br>

### `FluentChainMethodCallToNormalMethodCallRector`

- class: [`Rector\MagicDisclosure\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector`](/rules/magic-disclosure/src/Rector/MethodCall/FluentChainMethodCallToNormalMethodCallRector.php)
- [test fixtures](/rules/magic-disclosure/tests/Rector/MethodCall/FluentChainMethodCallToNormalMethodCallRector/Fixture)

Turns fluent interface calls to classic ones.

```diff
 $someClass = new SomeClass();
-$someClass->someFunction()
-            ->otherFunction();
+$someClass->someFunction();
+$someClass->otherFunction();
```

<br><br>

### `GetAndSetToMethodCallRector`

- class: [`Rector\MagicDisclosure\Rector\Assign\GetAndSetToMethodCallRector`](/rules/magic-disclosure/src/Rector/Assign/GetAndSetToMethodCallRector.php)
- [test fixtures](/rules/magic-disclosure/tests/Rector/Assign/GetAndSetToMethodCallRector/Fixture)

Turns defined `__get`/`__set` to specific method calls.

```php
<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\Assign\GetAndSetToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetAndSetToMethodCallRector::class)
        ->call(
            'configure',
            [[
                GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => [
                    'SomeContainer' => [
                        'set' => 'addService',
                    ],
                ],
            ]]
        );
};
```

↓

```diff
 $container = new SomeContainer;
-$container->someService = $someService;
+$container->setService("someService", $someService);
```
```php
<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\Assign\GetAndSetToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetAndSetToMethodCallRector::class)
        ->call(
            'configure',
            [[
                GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => [
                    'SomeContainer' => [
                        'get' => 'getService',
                    ],
                ],
            ]]
        );
};
```

↓

```diff
 $container = new SomeContainer;
-$someService = $container->someService;
+$someService = $container->getService("someService");
```

<br><br>

### `InArgFluentChainMethodCallToStandaloneMethodCallRector`

- class: [`Rector\MagicDisclosure\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector`](/rules/magic-disclosure/src/Rector/MethodCall/InArgFluentChainMethodCallToStandaloneMethodCallRector.php)

Turns fluent interface calls to classic ones.

```diff
 class UsedAsParameter
 {
     public function someFunction(FluentClass $someClass)
     {
-        $this->processFluentClass($someClass->someFunction()->otherFunction());
+        $someClass->someFunction();
+        $someClass->otherFunction();
+        $this->processFluentClass($someClass);
     }

     public function processFluentClass(FluentClass $someClass)
     {
     }
-}
+}
```

<br><br>

### `MethodCallOnSetterMethodCallToStandaloneAssignRector`

- class: [`Rector\MagicDisclosure\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector`](/rules/magic-disclosure/src/Rector/MethodCall/MethodCallOnSetterMethodCallToStandaloneAssignRector.php)
- [test fixtures](/rules/magic-disclosure/tests/Rector/MethodCall/MethodCallOnSetterMethodCallToStandaloneAssignRector/Fixture)

Change method call on setter to standalone assign before the setter

```diff
 class SomeClass
 {
     public function some()
     {
-        $this->anotherMethod(new AnotherClass())
-            ->someFunction();
+        $anotherClass = new AnotherClass();
+        $anotherClass->someFunction();
+        $this->anotherMethod($anotherClass);
     }

     public function anotherMethod(AnotherClass $anotherClass)
     {
     }
 }
```

<br><br>

### `ReturnThisRemoveRector`

- class: [`Rector\MagicDisclosure\Rector\ClassMethod\ReturnThisRemoveRector`](/rules/magic-disclosure/src/Rector/ClassMethod/ReturnThisRemoveRector.php)
- [test fixtures](/rules/magic-disclosure/tests/Rector/ClassMethod/ReturnThisRemoveRector/Fixture)

Removes "return `$this;"` from *fluent interfaces* for specified classes.

```diff
 class SomeExampleClass
 {
     public function someFunction()
     {
-        return $this;
     }

     public function otherFunction()
     {
-        return $this;
     }
 }
```

<br><br>

### `ToStringToMethodCallRector`

- class: [`Rector\MagicDisclosure\Rector\String_\ToStringToMethodCallRector`](/rules/magic-disclosure/src/Rector/String_/ToStringToMethodCallRector.php)
- [test fixtures](/rules/magic-disclosure/tests/Rector/String_/ToStringToMethodCallRector/Fixture)

Turns defined code uses of "__toString()" method  to specific method calls.

```php
<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\String_\ToStringToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ToStringToMethodCallRector::class)
        ->call('configure', [[
            ToStringToMethodCallRector::METHOD_NAMES_BY_TYPE => [
                'SomeObject' => 'getPath',
            ],
        ]]);
};
```

↓

```diff
 $someValue = new SomeObject;
-$result = (string) $someValue;
-$result = $someValue->__toString();
+$result = $someValue->getPath();
+$result = $someValue->getPath();
```

<br><br>

### `UnsetAndIssetToMethodCallRector`

- class: [`Rector\MagicDisclosure\Rector\Isset_\UnsetAndIssetToMethodCallRector`](/rules/magic-disclosure/src/Rector/Isset_/UnsetAndIssetToMethodCallRector.php)
- [test fixtures](/rules/magic-disclosure/tests/Rector/Isset_/UnsetAndIssetToMethodCallRector/Fixture)

Turns defined `__isset`/`__unset` calls to specific method calls.

```php
<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\MagicDisclosure\ValueObject\IssetUnsetToMethodCall;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(UnsetAndIssetToMethodCallRector::class)
        ->call(
            'configure',
            [[
                UnsetAndIssetToMethodCallRector::ISSET_UNSET_TO_METHOD_CALL => inline_value_objects(
                                [new IssetUnsetToMethodCall('SomeContainer', 'hasService', 'removeService')]
                            )
            ]]
        );
};
```

↓

```diff
 $container = new SomeContainer;
-isset($container["someKey"]);
+$container->hasService("someKey");
```
```php
<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\MagicDisclosure\ValueObject\IssetUnsetToMethodCall;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(UnsetAndIssetToMethodCallRector::class)
        ->call(
            'configure',
            [[
                UnsetAndIssetToMethodCallRector::ISSET_UNSET_TO_METHOD_CALL => inline_value_objects(
                                [new IssetUnsetToMethodCall('SomeContainer', 'hasService', 'removeService')]
                            )
            ]]
        );
};
```

↓

```diff
 $container = new SomeContainer;
-unset($container["someKey"]);
+$container->removeService("someKey");
```

<br><br>

## MockeryToProphecy

### `MockeryCloseRemoveRector`

- class: [`Rector\MockeryToProphecy\Rector\StaticCall\MockeryCloseRemoveRector`](/rules/mockery-to-prophecy/src/Rector/StaticCall/MockeryCloseRemoveRector.php)

Removes mockery close from test classes

```diff
 public function tearDown() : void
 {
-    \Mockery::close();
 }
```

<br><br>

### `MockeryCreateMockToProphizeRector`

- class: [`Rector\MockeryToProphecy\Rector\ClassMethod\MockeryCreateMockToProphizeRector`](/rules/mockery-to-prophecy/src/Rector/ClassMethod/MockeryCreateMockToProphizeRector.php)

Changes mockery mock creation to Prophesize

```diff
-$mock = \Mockery::mock(\'MyClass\');
+ $mock = $this->prophesize(\'MyClass\');
+
 $service = new Service();
-$service->injectDependency($mock);
+$service->injectDependency($mock->reveal());
```

<br><br>

## MockistaToMockery

### `MockeryTearDownRector`

- class: [`Rector\MockistaToMockery\Rector\Class_\MockeryTearDownRector`](/rules/mockista-to-mockery/src/Rector/Class_/MockeryTearDownRector.php)
- [test fixtures](/rules/mockista-to-mockery/tests/Rector/Class_/MockeryTearDownRector/Fixture)

Add `Mockery::close()` in `tearDown()` method if not yet

```diff
 use PHPUnit\Framework\TestCase;

 class SomeTest extends TestCase
 {
+    protected function tearDown(): void
+    {
+        Mockery::close();
+    }
     public function test()
     {
         $mockUser = mock(User::class);
     }
 }
```

<br><br>

### `MockistaMockToMockeryMockRector`

- class: [`Rector\MockistaToMockery\Rector\ClassMethod\MockistaMockToMockeryMockRector`](/rules/mockista-to-mockery/src/Rector/ClassMethod/MockistaMockToMockeryMockRector.php)
- [test fixtures](/rules/mockista-to-mockery/tests/Rector/ClassMethod/MockistaMockToMockeryMockRector/Fixture)

Change functions to static calls, so composer can autoload them

```diff
 class SomeTest
 {
     public function run()
     {
-        $mockUser = mock(User::class);
-        $mockUser->getId()->once->andReturn(1);
-        $mockUser->freeze();
+        $mockUser = Mockery::mock(User::class);
+        $mockUser->expects()->getId()->once()->andReturn(1);
     }
 }
```

<br><br>

## MysqlToMysqli

### `MysqlAssignToMysqliRector`

- class: [`Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector`](/rules/mysql-to-mysqli/src/Rector/Assign/MysqlAssignToMysqliRector.php)
- [test fixtures](/rules/mysql-to-mysqli/tests/Rector/Assign/MysqlAssignToMysqliRector/Fixture)

Converts more complex mysql functions to mysqli

```diff
-$data = mysql_db_name($result, $row);
+mysqli_data_seek($result, $row);
+$fetch = mysql_fetch_row($result);
+$data = $fetch[0];
```

<br><br>

### `MysqlFuncCallToMysqliRector`

- class: [`Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector`](/rules/mysql-to-mysqli/src/Rector/FuncCall/MysqlFuncCallToMysqliRector.php)
- [test fixtures](/rules/mysql-to-mysqli/tests/Rector/FuncCall/MysqlFuncCallToMysqliRector/Fixture)

Converts more complex mysql functions to mysqli

```diff
-mysql_drop_db($database);
+mysqli_query('DROP DATABASE ' . $database);
```

<br><br>

### `MysqlPConnectToMysqliConnectRector`

- class: [`Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector`](/rules/mysql-to-mysqli/src/Rector/FuncCall/MysqlPConnectToMysqliConnectRector.php)
- [test fixtures](/rules/mysql-to-mysqli/tests/Rector/FuncCall/MysqlPConnectToMysqliConnectRector/Fixture)

Replace `mysql_pconnect()` with `mysqli_connect()` with host p: prefix

```diff
 final class SomeClass
 {
     public function run($host, $username, $password)
     {
-        return mysql_pconnect($host, $username, $password);
+        return mysqli_connect('p:' . $host, $username, $password);
     }
 }
```

<br><br>

### `MysqlQueryMysqlErrorWithLinkRector`

- class: [`Rector\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector`](/rules/mysql-to-mysqli/src/Rector/FuncCall/MysqlQueryMysqlErrorWithLinkRector.php)
- [test fixtures](/rules/mysql-to-mysqli/tests/Rector/FuncCall/MysqlQueryMysqlErrorWithLinkRector/Fixture)

Add mysql_query and mysql_error with connection

```diff
 class SomeClass
 {
     public function run()
     {
         $conn = mysqli_connect('host', 'user', 'pass');

-        mysql_error();
+        mysqli_error($conn);
         $sql = 'SELECT';

-        return mysql_query($sql);
+        return mysqli_query($conn, $sql);
     }
 }
```

<br><br>

## Naming

### `MakeBoolPropertyRespectIsHasWasMethodNamingRector`

- class: [`Rector\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector`](/rules/naming/src/Rector/Property/MakeBoolPropertyRespectIsHasWasMethodNamingRector.php)
- [test fixtures](/rules/naming/tests/Rector/Property/MakeBoolPropertyRespectIsHasWasMethodNamingRector/Fixture)

Renames property to respect is/has/was method naming

```diff
 class SomeClass
 {
-    private $full = false;
+    private $isFull = false;

     public function isFull()
     {
-        return $this->full;
+        return $this->isFull;
     }
+
 }
```

<br><br>

### `MakeGetterClassMethodNameStartWithGetRector`

- class: [`Rector\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector`](/rules/naming/src/Rector/ClassMethod/MakeGetterClassMethodNameStartWithGetRector.php)
- [test fixtures](/rules/naming/tests/Rector/ClassMethod/MakeGetterClassMethodNameStartWithGetRector/Fixture)

Change getter method names to start with get/provide

```diff
 class SomeClass
 {
     /**
      * @var string
      */
     private $name;

-    public function name(): string
+    public function getName(): string
     {
         return $this->name;
     }
 }
```

<br><br>

### `MakeIsserClassMethodNameStartWithIsRector`

- class: [`Rector\Naming\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector`](/rules/naming/src/Rector/ClassMethod/MakeIsserClassMethodNameStartWithIsRector.php)
- [test fixtures](/rules/naming/tests/Rector/ClassMethod/MakeIsserClassMethodNameStartWithIsRector/Fixture)

Change is method names to start with is/has/was

```diff
 class SomeClass
 {
     /**
      * @var bool
      */
     private $isActive = false;

-    public function getIsActive()
+    public function isActive()
     {
         return $this->isActive;
     }
 }
```

<br><br>

### `RenameForeachValueVariableToMatchMethodCallReturnTypeRector`

- class: [`Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector`](/rules/naming/src/Rector/Foreach_/RenameForeachValueVariableToMatchMethodCallReturnTypeRector.php)
- [test fixtures](/rules/naming/tests/Rector/Foreach_/RenameForeachValueVariableToMatchMethodCallReturnTypeRector/Fixture)

Renames value variable name in foreach loop to match method type

```diff
 class SomeClass
 {
     public function run()
     {
         $array = [];
-        foreach ($object->getMethods() as $property) {
-            $array[] = $property;
+        foreach ($object->getMethods() as $method) {
+            $array[] = $method;
         }
     }
 }
```

<br><br>

### `RenameParamToMatchTypeRector`

- class: [`Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector`](/rules/naming/src/Rector/ClassMethod/RenameParamToMatchTypeRector.php)
- [test fixtures](/rules/naming/tests/Rector/ClassMethod/RenameParamToMatchTypeRector/Fixture)

Rename variable to match new ClassType

```diff
 final class SomeClass
 {
-    public function run(Apple $pie)
+    public function run(Apple $apple)
     {
-        $food = $pie;
+        $food = $apple;
     }
 }
```

<br><br>

### `RenamePropertyToMatchTypeRector`

- class: [`Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector`](/rules/naming/src/Rector/Class_/RenamePropertyToMatchTypeRector.php)
- [test fixtures](/rules/naming/tests/Rector/Class_/RenamePropertyToMatchTypeRector/Fixture)

Rename property and method param to match its type

```diff
 class SomeClass
 {
     /**
      * @var EntityManager
      */
-    private $eventManager;
+    private $entityManager;

-    public function __construct(EntityManager $eventManager)
+    public function __construct(EntityManager $entityManager)
     {
-        $this->eventManager = $eventManager;
+        $this->entityManager = $entityManager;
     }
 }
```

<br><br>

### `RenameVariableToMatchMethodCallReturnTypeRector`

- class: [`Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector`](/rules/naming/src/Rector/Assign/RenameVariableToMatchMethodCallReturnTypeRector.php)
- [test fixtures](/rules/naming/tests/Rector/Assign/RenameVariableToMatchMethodCallReturnTypeRector/Fixture)

Rename variable to match method return type

```diff
 class SomeClass
 {
     public function run()
     {
-        $a = $this->getRunner();
+        $runner = $this->getRunner();
     }

     public function getRunner(): Runner
     {
         return new Runner();
     }
 }
```

<br><br>

### `RenameVariableToMatchNewTypeRector`

- class: [`Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector`](/rules/naming/src/Rector/ClassMethod/RenameVariableToMatchNewTypeRector.php)
- [test fixtures](/rules/naming/tests/Rector/ClassMethod/RenameVariableToMatchNewTypeRector/Fixture)

Rename variable to match new ClassType

```diff
 final class SomeClass
 {
     public function run()
     {
-        $search = new DreamSearch();
-        $search->advance();
+        $dreamSearch = new DreamSearch();
+        $dreamSearch->advance();
     }
 }
```

<br><br>

### `UnderscoreToCamelCasePropertyNameRector`

- class: [`Rector\Naming\Rector\Property\UnderscoreToCamelCasePropertyNameRector`](/rules/naming/src/Rector/Property/UnderscoreToCamelCasePropertyNameRector.php)
- [test fixtures](/rules/naming/tests/Rector/Property/UnderscoreToCamelCasePropertyNameRector/Fixture)

Change under_score names to camelCase

```diff
 final class SomeClass
 {
-    public $property_name;
+    public $propertyName;

     public function run($a)
     {
-        $this->property_name = 5;
+        $this->propertyName = 5;
     }
 }
```

<br><br>

### `UnderscoreToCamelCaseVariableNameRector`

- class: [`Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector`](/rules/naming/src/Rector/Variable/UnderscoreToCamelCaseVariableNameRector.php)
- [test fixtures](/rules/naming/tests/Rector/Variable/UnderscoreToCamelCaseVariableNameRector/Fixture)

Change under_score names to camelCase

```diff
 final class SomeClass
 {
-    public function run($a_b)
+    public function run($aB)
     {
-        $some_value = $a_b;
+        $someValue = $aB;
     }
 }
```

<br><br>

## Nette

### `AddDatePickerToDateControlRector`

- class: [`Rector\Nette\Rector\MethodCall\AddDatePickerToDateControlRector`](/rules/nette/src/Rector/MethodCall/AddDatePickerToDateControlRector.php)
- [test fixtures](/rules/nette/tests/Rector/MethodCall/AddDatePickerToDateControlRector/Fixture)

Nextras/Form upgrade of addDatePicker method call to DateControl assign

```diff
 use Nette\Application\UI\Form;

 class SomeClass
 {
     public function run()
     {
         $form = new Form();
-        $form->addDatePicker('key', 'Label');
+        $form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label');
     }
 }
```

<br><br>

### `BuilderExpandToHelperExpandRector`

- class: [`Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector`](/rules/nette/src/Rector/MethodCall/BuilderExpandToHelperExpandRector.php)
- [test fixtures](/rules/nette/tests/Rector/MethodCall/BuilderExpandToHelperExpandRector/Fixture)

Change `containerBuilder->expand()` to static call with parameters

```diff
 use Nette\DI\CompilerExtension;

 final class SomeClass extends CompilerExtension
 {
     public function loadConfiguration()
     {
-        $value = $this->getContainerBuilder()->expand('%value');
+        $value = \Nette\DI\Helpers::expand('%value', $this->getContainerBuilder()->parameters);
     }
 }
```

<br><br>

### `ContextGetByTypeToConstructorInjectionRector`

- class: [`Rector\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector`](/rules/nette/src/Rector/MethodCall/ContextGetByTypeToConstructorInjectionRector.php)
- [test fixtures](/rules/nette/tests/Rector/MethodCall/ContextGetByTypeToConstructorInjectionRector/Fixture)

Move dependency get via `$context->getByType()` to constructor injection

```diff
 class SomeClass
 {
     /**
      * @var \Nette\DI\Container
      */
     private $context;

+    /**
+     * @var SomeTypeToInject
+     */
+    private $someTypeToInject;
+
+    public function __construct(SomeTypeToInject $someTypeToInject)
+    {
+        $this->someTypeToInject = $someTypeToInject;
+    }
+
     public function run()
     {
-        $someTypeToInject = $this->context->getByType(SomeTypeToInject::class);
+        $someTypeToInject = $this->someTypeToInject;
     }
 }
```

<br><br>

### `EndsWithFunctionToNetteUtilsStringsRector`

- class: [`Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector`](/rules/nette/src/Rector/Identical/EndsWithFunctionToNetteUtilsStringsRector.php)
- [test fixtures](/rules/nette/tests/Rector/Identical/EndsWithFunctionToNetteUtilsStringsRector/Fixture)

Use `Nette\Utils\Strings::endWith()` over bare string-functions

```diff
 class SomeClass
 {
     public function end($needle)
     {
         $content = 'Hi, my name is Tom';

-        $yes = substr($content, -strlen($needle)) === $needle;
+        $yes = \Nette\Utils\Strings::endsWith($content, $needle);
     }
 }
```

<br><br>

### `FilePutContentsToFileSystemWriteRector`

- class: [`Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector`](/rules/nette/src/Rector/FuncCall/FilePutContentsToFileSystemWriteRector.php)
- [test fixtures](/rules/nette/tests/Rector/FuncCall/FilePutContentsToFileSystemWriteRector/Fixture)

Change `file_put_contents()` to `FileSystem::write()`

```diff
 class SomeClass
 {
     public function run()
     {
-        file_put_contents('file.txt', 'content');
+        \Nette\Utils\FileSystem::write('file.txt', 'content');

         file_put_contents('file.txt', 'content_to_append', FILE_APPEND);
     }
 }
```

<br><br>

### `GetConfigWithDefaultsArgumentToArrayMergeInCompilerExtensionRector`

- class: [`Rector\Nette\Rector\MethodCall\GetConfigWithDefaultsArgumentToArrayMergeInCompilerExtensionRector`](/rules/nette/src/Rector/MethodCall/GetConfigWithDefaultsArgumentToArrayMergeInCompilerExtensionRector.php)
- [test fixtures](/rules/nette/tests/Rector/MethodCall/GetConfigWithDefaultsArgumentToArrayMergeInCompilerExtensionRector/Fixture)

Change `$this->getConfig($defaults)` to `array_merge`

```diff
 use Nette\DI\CompilerExtension;

 final class SomeExtension extends CompilerExtension
 {
     private $defaults = [
         'key' => 'value'
     ];

     public function loadConfiguration()
     {
-        $config = $this->getConfig($this->defaults);
+        $config = array_merge($this->defaults, $this->getConfig());
     }
 }
```

<br><br>

### `JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector`

- class: [`Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector`](/rules/nette/src/Rector/FuncCall/JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector.php)
- [test fixtures](/rules/nette/tests/Rector/FuncCall/JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector/Fixture)

Changes `json_encode()/json_decode()` to safer and more verbose `Nette\Utils\Json::encode()/decode()` calls

```diff
 class SomeClass
 {
     public function decodeJson(string $jsonString)
     {
-        $stdClass = json_decode($jsonString);
+        $stdClass = \Nette\Utils\Json::decode($jsonString);

-        $array = json_decode($jsonString, true);
-        $array = json_decode($jsonString, false);
+        $array = \Nette\Utils\Json::decode($jsonString, \Nette\Utils\Json::FORCE_ARRAY);
+        $array = \Nette\Utils\Json::decode($jsonString);
     }

     public function encodeJson(array $data)
     {
-        $jsonString = json_encode($data);
+        $jsonString = \Nette\Utils\Json::encode($data);

-        $prettyJsonString = json_encode($data, JSON_PRETTY_PRINT);
+        $prettyJsonString = \Nette\Utils\Json::encode($data, \Nette\Utils\Json::PRETTY);
     }
 }
```

<br><br>

### `MagicHtmlCallToAppendAttributeRector`

- class: [`Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector`](/rules/nette/src/Rector/MethodCall/MagicHtmlCallToAppendAttributeRector.php)
- [test fixtures](/rules/nette/tests/Rector/MethodCall/MagicHtmlCallToAppendAttributeRector/Fixture)

Change magic `addClass()` etc. calls on Html to explicit methods

```diff
 use Nette\Utils\Html;

 final class SomeClass
 {
     public function run()
     {
         $html = Html::el();
-        $html->setClass('first');
+        $html->appendAttribute('class', 'first');
     }
 }
```

<br><br>

### `PregFunctionToNetteUtilsStringsRector`

- class: [`Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector`](/rules/nette/src/Rector/FuncCall/PregFunctionToNetteUtilsStringsRector.php)
- [test fixtures](/rules/nette/tests/Rector/FuncCall/PregFunctionToNetteUtilsStringsRector/Fixture)

Use `Nette\Utils\Strings` over bare `preg_split()` and `preg_replace()` functions

```diff
+use Nette\Utils\Strings;
+
 class SomeClass
 {
     public function run()
     {
         $content = 'Hi my name is Tom';
-        $splitted = preg_split('#Hi#', $content);
+        $splitted = \Nette\Utils\Strings::split($content, '#Hi#');
     }
 }
```

<br><br>

### `PregMatchFunctionToNetteUtilsStringsRector`

- class: [`Rector\Nette\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector`](/rules/nette/src/Rector/FuncCall/PregMatchFunctionToNetteUtilsStringsRector.php)
- [test fixtures](/rules/nette/tests/Rector/FuncCall/PregMatchFunctionToNetteUtilsStringsRector/Fixture)

Use `Nette\Utils\Strings` over bare `preg_match()` and `preg_match_all()` functions

```diff
+use Nette\Utils\Strings;
+
 class SomeClass
 {
     public function run()
     {
         $content = 'Hi my name is Tom';
-        preg_match('#Hi#', $content, $matches);
+        $matches = Strings::match($content, '#Hi#');
     }
 }
```

<br><br>

### `SetClassWithArgumentToSetFactoryRector`

- class: [`Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector`](/rules/nette/src/Rector/MethodCall/SetClassWithArgumentToSetFactoryRector.php)
- [test fixtures](/rules/nette/tests/Rector/MethodCall/SetClassWithArgumentToSetFactoryRector/Fixture)

Change setClass with class and arguments to separated methods

```diff
 use Nette\DI\ContainerBuilder;

 class SomeClass
 {
     public function run(ContainerBuilder $containerBuilder)
     {
         $containerBuilder->addDefinition('...')
-            ->setClass('SomeClass', [1, 2]);
+            ->setFactory('SomeClass', [1, 2]);
     }
 }
```

<br><br>

### `StartsWithFunctionToNetteUtilsStringsRector`

- class: [`Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector`](/rules/nette/src/Rector/Identical/StartsWithFunctionToNetteUtilsStringsRector.php)
- [test fixtures](/rules/nette/tests/Rector/Identical/StartsWithFunctionToNetteUtilsStringsRector/Fixture)

Use `Nette\Utils\Strings::startsWith()` over bare string-functions

```diff
 class SomeClass
 {
     public function start($needle)
     {
         $content = 'Hi, my name is Tom';

-        $yes = substr($content, 0, strlen($needle)) === $needle;
+        $yes = \Nette\Utils\Strings::startsWith($content, $needle);
     }
 }
```

<br><br>

### `StrposToStringsContainsRector`

- class: [`Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector`](/rules/nette/src/Rector/NotIdentical/StrposToStringsContainsRector.php)
- [test fixtures](/rules/nette/tests/Rector/NotIdentical/StrposToStringsContainsRector/Fixture)

Use `Nette\Utils\Strings` over bare string-functions

```diff
 class SomeClass
 {
     public function run()
     {
         $name = 'Hi, my name is Tom';
-        return strpos($name, 'Hi') !== false;
+        return \Nette\Utils\Strings::contains($name, 'Hi');
     }
 }
```

<br><br>

### `SubstrStrlenFunctionToNetteUtilsStringsRector`

- class: [`Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector`](/rules/nette/src/Rector/FuncCall/SubstrStrlenFunctionToNetteUtilsStringsRector.php)
- [test fixtures](/rules/nette/tests/Rector/FuncCall/SubstrStrlenFunctionToNetteUtilsStringsRector/Fixture)

Use `Nette\Utils\Strings` over bare string-functions

```diff
 class SomeClass
 {
     public function run()
     {
-        return substr($value, 0, 3);
+        return \Nette\Utils\Strings::substring($value, 0, 3);
     }
 }
```

<br><br>

### `TemplateMagicAssignToExplicitVariableArrayRector`

- class: [`Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector`](/rules/nette/src/Rector/ClassMethod/TemplateMagicAssignToExplicitVariableArrayRector.php)
- [test fixtures](/rules/nette/tests/Rector/ClassMethod/TemplateMagicAssignToExplicitVariableArrayRector/Fixture)

Change `$this->templates->{magic}` to `$this->template->render(..., $values)`

```diff
 use Nette\Application\UI\Control;

 class SomeControl extends Control
 {
     public function render()
     {
-        $this->template->param = 'some value';
-        $this->template->render(__DIR__ . '/poll.latte');
+        $this->template->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
     }
 }
```

<br><br>

### `TranslateClassMethodToVariadicsRector`

- class: [`Rector\Nette\Rector\ClassMethod\TranslateClassMethodToVariadicsRector`](/rules/nette/src/Rector/ClassMethod/TranslateClassMethodToVariadicsRector.php)
- [test fixtures](/rules/nette/tests/Rector/ClassMethod/TranslateClassMethodToVariadicsRector/Fixture)

Change `translate()` method call 2nd arg to variadic

```diff
 use Nette\Localization\ITranslator;

 final class SomeClass implements ITranslator
 {
-    public function translate($message, $count = null)
+    public function translate($message, ... $parameters)
     {
+        $count = $parameters[0] ?? null;
         return [$message, $count];
     }
 }
```

<br><br>

## NetteCodeQuality

### `ArrayAccessGetControlToGetComponentMethodCallRector`

- class: [`Rector\NetteCodeQuality\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector`](/rules/nette-code-quality/src/Rector/Assign/ArrayAccessGetControlToGetComponentMethodCallRector.php)
- [test fixtures](/rules/nette-code-quality/tests/Rector/Assign/ArrayAccessGetControlToGetComponentMethodCallRector/Fixture)

Change magic arrays access get, to explicit `$this->getComponent(...)` method

```diff
 use Nette\Application\UI\Presenter;

 class SomeClass extends Presenter
 {
     public function some()
     {
-        $someControl = $this['whatever'];
+        $someControl = $this->getComponent('whatever');
     }
 }
```

<br><br>

### `ArrayAccessSetControlToAddComponentMethodCallRector`

- class: [`Rector\NetteCodeQuality\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector`](/rules/nette-code-quality/src/Rector/Assign/ArrayAccessSetControlToAddComponentMethodCallRector.php)
- [test fixtures](/rules/nette-code-quality/tests/Rector/Assign/ArrayAccessSetControlToAddComponentMethodCallRector/Fixture)

Change magic arrays access set, to explicit `$this->setComponent(...)` method

```diff
 use Nette\Application\UI\Control;
 use Nette\Application\UI\Presenter;

 class SomeClass extends Presenter
 {
     public function some()
     {
         $someControl = new Control();
-        $this['whatever'] = $someControl;
+        $this->addComponent($someControl, 'whatever');
     }
 }
```

<br><br>

### `ChangeControlArrayAccessToAnnotatedControlVariableRector`

- class: [`Rector\NetteCodeQuality\Rector\ArrayDimFetch\ChangeControlArrayAccessToAnnotatedControlVariableRector`](/rules/nette-code-quality/src/Rector/ArrayDimFetch/ChangeControlArrayAccessToAnnotatedControlVariableRector.php)
- [test fixtures](/rules/nette-code-quality/tests/Rector/ArrayDimFetch/ChangeControlArrayAccessToAnnotatedControlVariableRector/Fixture)

Change magic `$this["some_component"]` to variable assign with @var annotation

```diff
 use Nette\Application\UI\Presenter;
 use Nette\Application\UI\Form;

 final class SomePresenter extends Presenter
 {
     public function run()
     {
-        if ($this['some_form']->isSubmitted()) {
+        /** @var \Nette\Application\UI\Form $someForm */
+        $someForm = $this['some_form'];
+        if ($someForm->isSubmitted()) {
         }
     }

     protected function createComponentSomeForm()
     {
         return new Form();
     }
 }
```

<br><br>

### `ChangeFormArrayAccessToAnnotatedControlVariableRector`

- class: [`Rector\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector`](/rules/nette-code-quality/src/Rector/ArrayDimFetch/ChangeFormArrayAccessToAnnotatedControlVariableRector.php)
- [test fixtures](/rules/nette-code-quality/tests/Rector/ArrayDimFetch/ChangeFormArrayAccessToAnnotatedControlVariableRector/Fixture)

Change array access magic on `$form` to explicit standalone typed variable

```diff
 use Nette\Application\UI\Form;

 class SomePresenter
 {
     public function run()
     {
         $form = new Form();
         $this->addText('email', 'Email');

-        $form['email']->value = 'hey@hi.hello';
+        /** @var \Nette\Forms\Controls\TextInput $emailControl */
+        $emailControl = $form['email'];
+        $emailControl->value = 'hey@hi.hello';
     }
 }
```

<br><br>

### `MakeGetComponentAssignAnnotatedRector`

- class: [`Rector\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector`](/rules/nette-code-quality/src/Rector/Assign/MakeGetComponentAssignAnnotatedRector.php)
- [test fixtures](/rules/nette-code-quality/tests/Rector/Assign/MakeGetComponentAssignAnnotatedRector/Fixture)

Add doc type for magic `$control->getComponent(...)` assign

```diff
 use Nette\Application\UI\Control;

 final class SomeClass
 {
     public function run()
     {
         $externalControl = new ExternalControl();
+        /** @var AnotherControl $anotherControl */
         $anotherControl = $externalControl->getComponent('another');
     }
 }

 final class ExternalControl extends Control
 {
     public function createComponentAnother(): AnotherControl
     {
         return new AnotherControl();
     }
 }

 final class AnotherControl extends Control
 {
 }
```

<br><br>

### `MoveInjectToExistingConstructorRector`

- class: [`Rector\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector`](/rules/nette-code-quality/src/Rector/Class_/MoveInjectToExistingConstructorRector.php)
- [test fixtures](/rules/nette-code-quality/tests/Rector/Class_/MoveInjectToExistingConstructorRector/Fixture)

Move @inject properties to constructor, if there already is one

```diff
 final class SomeClass
 {
     /**
      * @var SomeDependency
-     * @inject
      */
-    public $someDependency;
+    private $someDependency;

     /**
      * @var OtherDependency
      */
     private $otherDependency;

-    public function __construct(OtherDependency $otherDependency)
+    public function __construct(OtherDependency $otherDependency, SomeDependency $someDependency)
     {
         $this->otherDependency = $otherDependency;
+        $this->someDependency = $someDependency;
     }
 }
```

<br><br>

## NetteKdyby

### `ChangeNetteEventNamesInGetSubscribedEventsRector`

- class: [`Rector\NetteKdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector`](/rules/nette-kdyby/src/Rector/ClassMethod/ChangeNetteEventNamesInGetSubscribedEventsRector.php)
- [test fixtures](/rules/nette-kdyby/tests/Rector/ClassMethod/ChangeNetteEventNamesInGetSubscribedEventsRector/Fixture)

Change EventSubscriber from Kdyby to Contributte

```diff
+use Contributte\Events\Extra\Event\Application\ShutdownEvent;
 use Kdyby\Events\Subscriber;
 use Nette\Application\Application;
-use Nette\Application\UI\Presenter;

 class GetApplesSubscriber implements Subscriber
 {
-    public function getSubscribedEvents()
+    public static function getSubscribedEvents()
     {
         return [
-            Application::class . '::onShutdown',
+            ShutdownEvent::class => 'onShutdown',
         ];
     }

-    public function onShutdown(Presenter $presenter)
+    public function onShutdown(ShutdownEvent $shutdownEvent)
     {
+        $presenter = $shutdownEvent->getPresenter();
         $presenterName = $presenter->getName();
         // ...
     }
 }
```

<br><br>

### `ReplaceEventManagerWithEventSubscriberRector`

- class: [`Rector\NetteKdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector`](/rules/nette-kdyby/src/Rector/MethodCall/ReplaceEventManagerWithEventSubscriberRector.php)
- [test fixtures](/rules/nette-kdyby/tests/Rector/MethodCall/ReplaceEventManagerWithEventSubscriberRector/Fixture)

Change Kdyby EventManager to EventDispatcher

```diff
 use Kdyby\Events\EventManager;

 final class SomeClass
 {
     /**
      * @var EventManager
      */
     private $eventManager;

     public function __construct(EventManager $eventManager)
     {
         $this->eventManager = eventManager;
     }

     public function run()
     {
         $key = '2000';
-        $this->eventManager->dispatchEvent(static::class . '::onCopy', new EventArgsList([$this, $key]));
+        $this->eventManager->dispatch(new SomeClassCopyEvent($this, $key));
     }
 }
```

<br><br>

### `ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector`

- class: [`Rector\NetteKdyby\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector`](/rules/nette-kdyby/src/Rector/ClassMethod/ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector.php)
- [test fixtures](/rules/nette-kdyby/tests/Rector/ClassMethod/ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector/Fixture)

Change `getSubscribedEvents()` from on magic property, to Event class

```diff
 use Kdyby\Events\Subscriber;

 final class ActionLogEventSubscriber implements Subscriber
 {
     public function getSubscribedEvents(): array
     {
         return [
-            AlbumService::class . '::onApprove' => 'onAlbumApprove',
+            AlbumServiceApproveEvent::class => 'onAlbumApprove',
         ];
     }

-    public function onAlbumApprove(Album $album, int $adminId): void
+    public function onAlbumApprove(AlbumServiceApproveEventAlbum $albumServiceApproveEventAlbum): void
     {
+        $album = $albumServiceApproveEventAlbum->getAlbum();
         $album->play();
     }
 }
```

<br><br>

### `ReplaceMagicPropertyEventWithEventClassRector`

- class: [`Rector\NetteKdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector`](/rules/nette-kdyby/src/Rector/MethodCall/ReplaceMagicPropertyEventWithEventClassRector.php)
- [test fixtures](/rules/nette-kdyby/tests/Rector/MethodCall/ReplaceMagicPropertyEventWithEventClassRector/Fixture)

Change `$onProperty` magic call with event disptacher and class dispatch

```diff
 final class FileManager
 {
-    public $onUpload;
+    use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

+    public function __construct(EventDispatcherInterface $eventDispatcher)
+    {
+        $this->eventDispatcher = $eventDispatcher;
+    }
+
     public function run(User $user)
     {
-        $this->onUpload($user);
+        $onFileManagerUploadEvent = new FileManagerUploadEvent($user);
+        $this->eventDispatcher->dispatch($onFileManagerUploadEvent);
     }
 }
```

<br><br>

## NetteTesterToPHPUnit

### `NetteAssertToPHPUnitAssertRector`

- class: [`Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector`](/rules/nette-tester-to-phpunit/src/Rector/StaticCall/NetteAssertToPHPUnitAssertRector.php)

Migrate Nette/Assert calls to PHPUnit

```diff
 use Tester\Assert;

 function someStaticFunctions()
 {
-    Assert::true(10 == 5);
+    \PHPUnit\Framework\Assert::assertTrue(10 == 5);
 }
```

<br><br>

### `NetteTesterClassToPHPUnitClassRector`

- class: [`Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector`](/rules/nette-tester-to-phpunit/src/Rector/Class_/NetteTesterClassToPHPUnitClassRector.php)

Migrate Nette Tester test case to PHPUnit

```diff
 namespace KdybyTests\Doctrine;

 use Tester\TestCase;
 use Tester\Assert;

-require_once __DIR__ . '/../bootstrap.php';
-
-class ExtensionTest extends TestCase
+class ExtensionTest extends \PHPUnit\Framework\TestCase
 {
     public function testFunctionality()
     {
-        Assert::true($default instanceof Kdyby\Doctrine\EntityManager);
-        Assert::true(5);
-        Assert::same($container->getService('kdyby.doctrine.default.entityManager'), $default);
+        $this->assertInstanceOf(\Kdyby\Doctrine\EntityManager::cllass, $default);
+        $this->assertTrue(5);
+        $this->same($container->getService('kdyby.doctrine.default.entityManager'), $default);
     }
-}
-
-(new \ExtensionTest())->run();
+}
```

<br><br>

### `RenameTesterTestToPHPUnitToTestFileRector`

- class: [`Rector\NetteTesterToPHPUnit\Rector\RenameTesterTestToPHPUnitToTestFileRector`](/rules/nette-tester-to-phpunit/src/Rector/RenameTesterTestToPHPUnitToTestFileRector.php)

Rename "*.phpt" file to "*Test.php" file

```diff
-// tests/SomeTestCase.phpt
+// tests/SomeTestCase.php
```

<br><br>

## NetteToSymfony

### `DeleteFactoryInterfaceRector`

- class: [`Rector\NetteToSymfony\Rector\FileSystem\DeleteFactoryInterfaceRector`](/rules/nette-to-symfony/src/Rector/FileSystem/DeleteFactoryInterfaceRector.php)

Interface factories are not needed in Symfony. Clear constructor injection is used instead

```diff
-interface SomeControlFactoryInterface
-{
-    public function create();
-}
```

<br><br>

### `FormControlToControllerAndFormTypeRector`

- class: [`Rector\NetteToSymfony\Rector\Class_\FormControlToControllerAndFormTypeRector`](/rules/nette-to-symfony/src/Rector/Class_/FormControlToControllerAndFormTypeRector.php)
- [test fixtures](/rules/nette-to-symfony/tests/Rector/Class_/FormControlToControllerAndFormTypeRector/Fixture)

Change Form that extends Control to Controller and decoupled FormType

```diff
-use Nette\Application\UI\Form;
-use Nette\Application\UI\Control;
-
-class SomeForm extends Control
+class SomeFormController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 {
-    public function createComponentForm()
+    /**
+     * @Route(...)
+     */
+    public function actionSomeForm(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
     {
-        $form = new Form();
-        $form->addText('name', 'Your name');
+        $form = $this->createForm(SomeFormType::class);
+        $form->handleRequest($request);

-        $form->onSuccess[] = [$this, 'processForm'];
-    }
-
-    public function processForm(Form $form)
-    {
-        // process me
+        if ($form->isSuccess() && $form->isValid()) {
+            // process me
+        }
     }
 }
```

**New file**
```php

declare(strict_types=1);

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SomeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder->add('name', TextType::class, [
            'label' => 'Your name',
        ]);
    }
}
```

<br><br>

### `FromHttpRequestGetHeaderToHeadersGetRector`

- class: [`Rector\NetteToSymfony\Rector\MethodCall\FromHttpRequestGetHeaderToHeadersGetRector`](/rules/nette-to-symfony/src/Rector/MethodCall/FromHttpRequestGetHeaderToHeadersGetRector.php)
- [test fixtures](/rules/nette-to-symfony/tests/Rector/MethodCall/FromHttpRequestGetHeaderToHeadersGetRector/Fixture)

Changes `getHeader()` to `$request->headers->get()`

```diff
 use Nette\Request;

 final class SomeController
 {
     public static function someAction(Request $request)
     {
-        $header = $this->httpRequest->getHeader('x');
+        $header = $request->headers->get('x');
     }
 }
```

<br><br>

### `FromRequestGetParameterToAttributesGetRector`

- class: [`Rector\NetteToSymfony\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector`](/rules/nette-to-symfony/src/Rector/MethodCall/FromRequestGetParameterToAttributesGetRector.php)
- [test fixtures](/rules/nette-to-symfony/tests/Rector/MethodCall/FromRequestGetParameterToAttributesGetRector/Fixture)

Changes "getParameter()" to "attributes->get()" from Nette to Symfony

```diff
 use Nette\Request;

 final class SomeController
 {
     public static function someAction(Request $request)
     {
-        $value = $request->getParameter('abz');
+        $value = $request->attribute->get('abz');
     }
 }
```

<br><br>

### `NetteControlToSymfonyControllerRector`

- class: [`Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector`](/rules/nette-to-symfony/src/Rector/Class_/NetteControlToSymfonyControllerRector.php)
- [test fixtures](/rules/nette-to-symfony/tests/Rector/Class_/NetteControlToSymfonyControllerRector/Fixture)

Migrate Nette Component to Symfony Controller

```diff
-use Nette\Application\UI\Control;
+use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+use Symfony\Component\HttpFoundation\Response;

-class SomeControl extends Control
+class SomeController extends AbstractController
 {
-    public function render()
-    {
-        $this->template->param = 'some value';
-        $this->template->render(__DIR__ . '/poll.latte');
-    }
+     public function some(): Response
+     {
+         return $this->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
+     }
 }
```

<br><br>

### `NetteFormToSymfonyFormRector`

- class: [`Rector\NetteToSymfony\Rector\MethodCall\NetteFormToSymfonyFormRector`](/rules/nette-to-symfony/src/Rector/MethodCall/NetteFormToSymfonyFormRector.php)
- [test fixtures](/rules/nette-to-symfony/tests/Rector/MethodCall/NetteFormToSymfonyFormRector/Fixture)

Migrate Nette\Forms in Presenter to Symfony

```diff
 use Nette\Application\UI;

 class SomePresenter extends UI\Presenter
 {
     public function someAction()
     {
-        $form = new UI\Form;
-        $form->addText('name', 'Name:');
-        $form->addPassword('password', 'Password:');
-        $form->addSubmit('login', 'Sign up');
+        $form = $this->createFormBuilder();
+        $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
+            'label' => 'Name:'
+        ]);
+        $form->add('password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, [
+            'label' => 'Password:'
+        ]);
+        $form->add('login', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
+            'label' => 'Sign up'
+        ]);
     }
 }
```

<br><br>

### `RenameEventNamesInEventSubscriberRector`

- class: [`Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector`](/rules/nette-to-symfony/src/Rector/ClassMethod/RenameEventNamesInEventSubscriberRector.php)
- [test fixtures](/rules/nette-to-symfony/tests/Rector/ClassMethod/RenameEventNamesInEventSubscriberRector/Fixture)

Changes event names from Nette ones to Symfony ones

```diff
 use Symfony\Component\EventDispatcher\EventSubscriberInterface;

 final class SomeClass implements EventSubscriberInterface
 {
     public static function getSubscribedEvents()
     {
-        return ['nette.application' => 'someMethod'];
+        return [\SymfonyEvents::KERNEL => 'someMethod'];
     }
 }
```

<br><br>

### `RouterListToControllerAnnotationsRector`

- class: [`Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector`](/rules/nette-to-symfony/src/Rector/ClassMethod/RouterListToControllerAnnotationsRector.php)
- [test fixtures](/rules/nette-to-symfony/tests/Rector/ClassMethod/RouterListToControllerAnnotationsRetor/Fixture)

Change new `Route()` from RouteFactory to @Route annotation above controller method

```diff
 final class RouterFactory
 {
     public function create(): RouteList
     {
         $routeList = new RouteList();
+
+        // case of single action controller, usually get() or __invoke() method
         $routeList[] = new Route('some-path', SomePresenter::class);

         return $routeList;
     }
 }

+use Symfony\Component\Routing\Annotation\Route;
+
 final class SomePresenter
 {
+    /**
+     * @Route(path="some-path")
+     */
     public function run()
     {
     }
 }
```

<br><br>

### `WrapTransParameterNameRector`

- class: [`Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector`](/rules/nette-to-symfony/src/Rector/MethodCall/WrapTransParameterNameRector.php)
- [test fixtures](/rules/nette-to-symfony/tests/Rector/MethodCall/WrapTransParameterNameRector/Fixture)

Adds %% to placeholder name of `trans()` method if missing

```diff
 use Symfony\Component\Translation\Translator;

 final class SomeController
 {
     public function run()
     {
         $translator = new Translator('');
         $translated = $translator->trans(
             'Hello %name%',
-            ['name' => $name]
+            ['%name%' => $name]
         );
     }
 }
```

<br><br>

## NetteUtilsCodeQuality

### `ReplaceTimeNumberWithDateTimeConstantRector`

- class: [`Rector\NetteUtilsCodeQuality\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector`](/rules/nette-utils-code-quality/src/Rector/LNumber/ReplaceTimeNumberWithDateTimeConstantRector.php)
- [test fixtures](/rules/nette-utils-code-quality/tests/Rector/LNumber/ReplaceTimeNumberWithDateTimeConstantRector/Fixture)

Replace `time` numbers with `Nette\Utils\DateTime` constants

```diff
 final class SomeClass
 {
     public function run()
     {
-        return 86400;
+        return \Nette\Utils\DateTime::DAY;
     }
 }
```

<br><br>

## Order

### `OrderClassConstantsByIntegerValueRector`

- class: [`Rector\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector`](/rules/order/src/Rector/Class_/OrderClassConstantsByIntegerValueRector.php)
- [test fixtures](/rules/order/tests/Rector/Class_/OrderClassConstantsByIntegerValueRector/Fixture)

Order class constant order by their integer value

```diff
 class SomeClass
 {
     const MODE_ON = 0;

+    const MODE_MAYBE = 1;
+
     const MODE_OFF = 2;
-
-    const MODE_MAYBE = 1;
 }
```

<br><br>

### `OrderConstantsByVisibilityRector`

- class: [`Rector\Order\Rector\Class_\OrderConstantsByVisibilityRector`](/rules/order/src/Rector/Class_/OrderConstantsByVisibilityRector.php)
- [test fixtures](/rules/order/tests/Rector/Class_/OrderConstantsByVisibilityRector/Fixture)

Orders constants by visibility

```diff
 final class SomeClass
 {
+    public const PUBLIC_CONST = 'public';
+    protected const PROTECTED_CONST = 'protected';
     private const PRIVATE_CONST = 'private';
-    protected const PROTECTED_CONST = 'protected';
-    public const PUBLIC_CONST = 'public';
 }
```

<br><br>

### `OrderConstructorDependenciesByTypeAlphabeticallyRector`

- class: [`Rector\Order\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector`](/rules/order/src/Rector/ClassMethod/OrderConstructorDependenciesByTypeAlphabeticallyRector.php)
- [test fixtures](/rules/order/tests/Rector/ClassMethod/OrderConstructorDependenciesByTypeAlphabeticallyRector/Fixture)

Order __constructor dependencies by type A-Z

```php
<?php

declare(strict_types=1);

use Rector\Order\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(OrderConstructorDependenciesByTypeAlphabeticallyRector::class)
        ->call(
            'configure',
            [[
                OrderConstructorDependenciesByTypeAlphabeticallyRector::SKIP_PATTERNS => ['Cla*ame', 'Ano?herClassName'],
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
     public function __construct(
+        LatteAndTwigFinder $latteAndTwigFinder,
         LatteToTwigConverter $latteToTwigConverter,
-        SymfonyStyle $symfonyStyle,
-        LatteAndTwigFinder $latteAndTwigFinder,
-        SmartFileSystem $smartFileSystem
+        SmartFileSystem $smartFileSystem,
+        SymfonyStyle $symfonyStyle
     ) {
     }
 }
```

<br><br>

### `OrderFirstLevelClassStatementsRector`

- class: [`Rector\Order\Rector\Class_\OrderFirstLevelClassStatementsRector`](/rules/order/src/Rector/Class_/OrderFirstLevelClassStatementsRector.php)
- [test fixtures](/rules/order/tests/Rector/Class_/OrderFirstLevelClassStatementsRector/Fixture)

Orders first level Class statements

```diff
 final class SomeClass
 {
+    use TraitName;
+    private const CONST_NAME = 'constant_value';
+    protected $propertyName;
     public function functionName();
-    protected $propertyName;
-    private const CONST_NAME = 'constant_value';
-    use TraitName;
 }
```

<br><br>

### `OrderMethodsByVisibilityRector`

- class: [`Rector\Order\Rector\Class_\OrderMethodsByVisibilityRector`](/rules/order/src/Rector/Class_/OrderMethodsByVisibilityRector.php)
- [test fixtures](/rules/order/tests/Rector/Class_/OrderMethodsByVisibilityRector/Fixture)

Orders method by visibility

```diff
 class SomeClass
 {
+    public function publicFunctionName();
     protected function protectedFunctionName();
     private function privateFunctionName();
-    public function publicFunctionName();
 }
```

<br><br>

### `OrderPrivateMethodsByUseRector`

- class: [`Rector\Order\Rector\Class_\OrderPrivateMethodsByUseRector`](/rules/order/src/Rector/Class_/OrderPrivateMethodsByUseRector.php)
- [test fixtures](/rules/order/tests/Rector/Class_/OrderPrivateMethodsByUseRector/Fixture)

Order private methods in order of their use

```diff
 class SomeClass
 {
     public function run()
     {
         $this->call1();
         $this->call2();
     }

-    private function call2()
+    private function call1()
     {
     }

-    private function call1()
+    private function call2()
     {
     }
 }
```

<br><br>

### `OrderPropertiesByVisibilityRector`

- class: [`Rector\Order\Rector\Class_\OrderPropertiesByVisibilityRector`](/rules/order/src/Rector/Class_/OrderPropertiesByVisibilityRector.php)
- [test fixtures](/rules/order/tests/Rector/Class_/OrderPropertiesByVisibilityRector/Fixture)

Orders properties by visibility

```diff
 final class SomeClass
 {
+    public $publicProperty;
     protected $protectedProperty;
     private $privateProperty;
-    public $publicProperty;
 }
```

<br><br>

### `OrderPropertyByComplexityRector`

- class: [`Rector\Order\Rector\Class_\OrderPropertyByComplexityRector`](/rules/order/src/Rector/Class_/OrderPropertyByComplexityRector.php)
- [test fixtures](/rules/order/tests/Rector/Class_/OrderPropertyByComplexityRector/Fixture)

Order properties by complexity, from the simplest like scalars to the most complex, like union or collections

```diff
-class SomeClass
+class SomeClass implements FoodRecipeInterface
 {
     /**
      * @var string
      */
     private $name;

     /**
-     * @var Type
+     * @var int
      */
-    private $service;
+    private $price;

     /**
-     * @var int
+     * @var Type
      */
-    private $price;
+    private $service;
 }
```

<br><br>

### `OrderPublicInterfaceMethodRector`

- class: [`Rector\Order\Rector\Class_\OrderPublicInterfaceMethodRector`](/rules/order/src/Rector/Class_/OrderPublicInterfaceMethodRector.php)
- [test fixtures](/rules/order/tests/Rector/Class_/OrderPublicInterfaceMethodRector/Fixture)

Order public methods required by interface in custom orderer

```php
<?php

declare(strict_types=1);

use Rector\Order\Rector\Class_\OrderPublicInterfaceMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(OrderPublicInterfaceMethodRector::class)
        ->call(
            'configure',
            [[
                OrderPublicInterfaceMethodRector::METHOD_ORDER_BY_INTERFACES => [
                    'FoodRecipeInterface' => [
                        'getDescription',
                        'process',
                    ],
                ],
            ]]
        );
};
```

↓

```diff
 class SomeClass implements FoodRecipeInterface
 {
-    public function process()
+    public function getDescription()
     {
     }
-
-    public function getDescription()
+    public function process()
     {
     }
 }
```

<br><br>

## PHPOffice

### `AddRemovedDefaultValuesRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\AddRemovedDefaultValuesRector`](/rules/php-office/src/Rector/StaticCall/AddRemovedDefaultValuesRector.php)
- [test fixtures](/rules/php-office/tests/Rector/StaticCall/AddRemovedDefaultValuesRector/Fixture)

Complete removed default values explicitly

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $logger = new \PHPExcel_CalcEngine_Logger;
-        $logger->setWriteDebugLog();
+        $logger->setWriteDebugLog(false);
     }
 }
```

<br><br>

### `CellStaticToCoordinateRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\CellStaticToCoordinateRector`](/rules/php-office/src/Rector/StaticCall/CellStaticToCoordinateRector.php)
- [test fixtures](/rules/php-office/tests/Rector/StaticCall/CellStaticToCoordinateRector/Fixture)

Methods to manipulate coordinates that used to exists in PHPExcel_Cell to PhpOffice\PhpSpreadsheet\Cell\Coordinate

```diff
 class SomeClass
 {
     public function run()
     {
-        \PHPExcel_Cell::stringFromColumnIndex();
+        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex();
     }
 }
```

<br><br>

### `ChangeChartRendererRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeChartRendererRector`](/rules/php-office/src/Rector/StaticCall/ChangeChartRendererRector.php)
- [test fixtures](/rules/php-office/tests/Rector/StaticCall/ChangeChartRendererRector/Fixture)

Change chart renderer

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        \PHPExcel_Settings::setChartRenderer($rendererName, $rendererLibraryPath);
+        \PHPExcel_Settings::setChartRenderer(\PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraph::class);
     }
 }
```

<br><br>

### `ChangeConditionalGetConditionRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeConditionalGetConditionRector`](/rules/php-office/src/Rector/MethodCall/ChangeConditionalGetConditionRector.php)
- [test fixtures](/rules/php-office/tests/Rector/MethodCall/ChangeConditionalGetConditionRector/Fixture)

Change argument `PHPExcel_Style_Conditional->getCondition()` to `getConditions()`

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $conditional = new \PHPExcel_Style_Conditional;
-        $someCondition = $conditional->getCondition();
+        $someCondition = $conditional->getConditions()[0] ?? '';
     }
 }
```

<br><br>

### `ChangeConditionalReturnedCellRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeConditionalReturnedCellRector`](/rules/php-office/src/Rector/MethodCall/ChangeConditionalReturnedCellRector.php)
- [test fixtures](/rules/php-office/tests/Rector/MethodCall/ChangeConditionalReturnedCellRector/Fixture)

Change conditional call to `getCell()`

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $cell = $worksheet->setCellValue('A1', 'value', true);
+        $cell = $worksheet->getCell('A1')->setValue('value');
     }
 }
```

<br><br>

### `ChangeConditionalSetConditionRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeConditionalSetConditionRector`](/rules/php-office/src/Rector/MethodCall/ChangeConditionalSetConditionRector.php)
- [test fixtures](/rules/php-office/tests/Rector/MethodCall/ChangeConditionalSetConditionRector/Fixture)

Change argument `PHPExcel_Style_Conditional->setCondition()` to `setConditions()`

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $conditional = new \PHPExcel_Style_Conditional;
-        $someCondition = $conditional->setCondition(1);
+        $someCondition = $conditional->setConditions((array) 1);
     }
 }
```

<br><br>

### `ChangeDataTypeForValueRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeDataTypeForValueRector`](/rules/php-office/src/Rector/StaticCall/ChangeDataTypeForValueRector.php)
- [test fixtures](/rules/php-office/tests/Rector/StaticCall/ChangeDataTypeForValueRector/Fixture)

Change argument `DataType::dataTypeForValue()` to DefaultValueBinder

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        $type = \PHPExcel_Cell_DataType::dataTypeForValue('value');
+        $type = \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder::dataTypeForValue('value');
     }
 }
```

<br><br>

### `ChangeDuplicateStyleArrayToApplyFromArrayRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeDuplicateStyleArrayToApplyFromArrayRector`](/rules/php-office/src/Rector/MethodCall/ChangeDuplicateStyleArrayToApplyFromArrayRector.php)
- [test fixtures](/rules/php-office/tests/Rector/MethodCall/ChangeDuplicateStyleArrayToApplyFromArrayRector/Fixture)

Change method call `duplicateStyleArray()` to `getStyle()` + `applyFromArray()`

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $worksheet->duplicateStyleArray($styles, $range, $advanced);
+        $worksheet->getStyle($range)->applyFromArray($styles, $advanced);
     }
 }
```

<br><br>

### `ChangeIOFactoryArgumentRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeIOFactoryArgumentRector`](/rules/php-office/src/Rector/StaticCall/ChangeIOFactoryArgumentRector.php)
- [test fixtures](/rules/php-office/tests/Rector/StaticCall/ChangeIOFactoryArgumentRector/Fixture)

Change argument of PHPExcel_IOFactory::createReader(), `PHPExcel_IOFactory::createWriter()` and `PHPExcel_IOFactory::identify()`

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        $writer = \PHPExcel_IOFactory::createWriter('CSV');
+        $writer = \PHPExcel_IOFactory::createWriter('Csv');
     }
 }
```

<br><br>

### `ChangePdfWriterRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangePdfWriterRector`](/rules/php-office/src/Rector/StaticCall/ChangePdfWriterRector.php)
- [test fixtures](/rules/php-office/tests/Rector/StaticCall/ChangePdfWriterRector/Fixture)

Change init of PDF writer

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        \PHPExcel_Settings::setPdfRendererName(PHPExcel_Settings::PDF_RENDERER_MPDF);
-        \PHPExcel_Settings::setPdfRenderer($somePath);
-        $writer = \PHPExcel_IOFactory::createWriter($spreadsheet, 'PDF');
+        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
     }
 }
```

<br><br>

### `ChangeSearchLocationToRegisterReaderRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeSearchLocationToRegisterReaderRector`](/rules/php-office/src/Rector/StaticCall/ChangeSearchLocationToRegisterReaderRector.php)
- [test fixtures](/rules/php-office/tests/Rector/StaticCall/ChangeSearchLocationToRegisterReaderRector/Fixture)

Change argument `addSearchLocation()` to `registerReader()`

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        \PHPExcel_IOFactory::addSearchLocation($type, $location, $classname);
+        \PhpOffice\PhpSpreadsheet\IOFactory::registerReader($type, $classname);
     }
 }
```

<br><br>

### `GetDefaultStyleToGetParentRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\GetDefaultStyleToGetParentRector`](/rules/php-office/src/Rector/MethodCall/GetDefaultStyleToGetParentRector.php)
- [test fixtures](/rules/php-office/tests/Rector/MethodCall/GetDefaultStyleToGetParentRector/Fixture)

Methods to (new `Worksheet())->getDefaultStyle()` to `getParent()->getDefaultStyle()`

```diff
 class SomeClass
 {
     public function run()
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $worksheet->getDefaultStyle();
+        $worksheet->getParent()->getDefaultStyle();
     }
 }
```

<br><br>

### `IncreaseColumnIndexRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\IncreaseColumnIndexRector`](/rules/php-office/src/Rector/MethodCall/IncreaseColumnIndexRector.php)
- [test fixtures](/rules/php-office/tests/Rector/MethodCall/IncreaseColumnIndexRector/Fixture)

Column index changed from 0 to 1 - run only ONCE! changes current value without memory

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $worksheet->setCellValueByColumnAndRow(0, 3, '1150');
+        $worksheet->setCellValueByColumnAndRow(1, 3, '1150');
     }
 }
```

<br><br>

### `RemoveSetTempDirOnExcelWriterRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\RemoveSetTempDirOnExcelWriterRector`](/rules/php-office/src/Rector/MethodCall/RemoveSetTempDirOnExcelWriterRector.php)
- [test fixtures](/rules/php-office/tests/Rector/MethodCall/RemoveSetTempDirOnExcelWriterRector/Fixture)

Remove `setTempDir()` on PHPExcel_Writer_Excel5

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $writer = new \PHPExcel_Writer_Excel5;
-        $writer->setTempDir();
     }
 }
```

<br><br>

## PHPStan

### `PHPStormVarAnnotationRector`

- class: [`Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector`](/rules/phpstan/src/Rector/Assign/PHPStormVarAnnotationRector.php)
- [test fixtures](/rules/phpstan/tests/Rector/Assign/PHPStormVarAnnotationRector/Fixture)

Change various @var annotation formats to one PHPStorm understands

```diff
-$config = 5;
-/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+$config = 5;
```

<br><br>

### `RecastingRemovalRector`

- class: [`Rector\PHPStan\Rector\Cast\RecastingRemovalRector`](/rules/phpstan/src/Rector/Cast/RecastingRemovalRector.php)
- [test fixtures](/rules/phpstan/tests/Rector/Cast/RecastingRemovalRector/Fixture)

Removes recasting of the same type

```diff
 $string = '';
-$string = (string) $string;
+$string = $string;

 $array = [];
-$array = (array) $array;
+$array = $array;
```

<br><br>

### `RemoveNonExistingVarAnnotationRector`

- class: [`Rector\PHPStan\Rector\Node\RemoveNonExistingVarAnnotationRector`](/rules/phpstan/src/Rector/Node/RemoveNonExistingVarAnnotationRector.php)
- [test fixtures](/rules/phpstan/tests/Rector/Node/RemoveNonExistingVarAnnotationRector/Fixture)

Removes non-existing @var annotations above the code

```diff
 class SomeClass
 {
     public function get()
     {
-        /** @var Training[] $trainings */
         return $this->getData();
     }
 }
```

<br><br>

## PHPUnit

### `AddDoesNotPerformAssertionToNonAssertingTestRector`

- class: [`Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector`](/rules/phpunit/src/Rector/ClassMethod/AddDoesNotPerformAssertionToNonAssertingTestRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/ClassMethod/AddDoesNotPerformAssertionToNonAssertingTestRector/Fixture)

Tests without assertion will have @doesNotPerformAssertion

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
+    /**
+     * @doesNotPerformAssertions
+     */
     public function test()
     {
         $nothing = 5;
     }
 }
```

<br><br>

### `AddProphecyTraitRector`

- class: [`Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector`](/rules/phpunit/src/Rector/Class_/AddProphecyTraitRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/Class_/AddProphecyTraitRector/Fixture)

Add Prophecy trait for method using `$this->prophesize()`

```diff
 use PHPUnit\Framework\TestCase;
+use Prophecy\PhpUnit\ProphecyTrait;

 final class ExampleTest extends TestCase
 {
+    use ProphecyTrait;
+
     public function testOne(): void
     {
         $prophecy = $this->prophesize(\AnInterface::class);
     }
 }
```

<br><br>

### `AddSeeTestAnnotationRector`

- class: [`Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector`](/rules/phpunit/src/Rector/Class_/AddSeeTestAnnotationRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/Class_/AddSeeTestAnnotationRector/Fixture)

Add @see annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.

```diff
+/**
+ * @see \SomeServiceTest
+ */
 class SomeService
 {
 }

 use PHPUnit\Framework\TestCase;

 class SomeServiceTest extends TestCase
 {
 }
```

<br><br>

### `ArrayArgumentInTestToDataProviderRector`

- class: [`Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector`](/rules/phpunit/src/Rector/Class_/ArrayArgumentInTestToDataProviderRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/Class_/ArrayArgumentInTestToDataProviderRector/Fixture)

Move array argument from tests into data provider [configurable]

```php
<?php

declare(strict_types=1);

use Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector;
use Rector\PHPUnit\ValueObject\ArrayArgumentToDataProvider;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayArgumentInTestToDataProviderRector::class)
        ->call(
            'configure',
            [[
                ArrayArgumentInTestToDataProviderRector::ARRAY_ARGUMENTS_TO_DATA_PROVIDERS => inline_value_objects(
                                [new ArrayArgumentToDataProvider(
                                    'PHPUnit\Framework\TestCase',
                                    'doTestMultiple',
                                    'doTestSingle',
                                    'number'
                                )]
                            )
            ]]
        );
};
```

↓

```diff
 use PHPUnit\Framework\TestCase;

 class SomeServiceTest extends TestCase
 {
-    public function test()
+    /**
+     * @dataProvider provideData()
+     */
+    public function test(int $number)
     {
-        $this->doTestMultiple([1, 2, 3]);
+        $this->doTestSingle($number);
+    }
+
+    public function provideData(): \Iterator
+    {
+        yield [1];
+        yield [2];
+        yield [3];
     }
 }
```

<br><br>

### `AssertCompareToSpecificMethodRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector`](/rules/phpunit/src/Rector/MethodCall/AssertCompareToSpecificMethodRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertCompareToSpecificMethodRector/Fixture)

Turns vague php-only method in PHPUnit TestCase to more specific

```diff
-$this->assertSame(10, count($anything), "message");
+$this->assertCount(10, $anything, "message");
```
```diff
-$this->assertNotEquals(get_class($value), stdClass::class);
+$this->assertNotInstanceOf(stdClass::class, $value);
```

<br><br>

### `AssertComparisonToSpecificMethodRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector`](/rules/phpunit/src/Rector/MethodCall/AssertComparisonToSpecificMethodRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertComparisonToSpecificMethodRector/Fixture)

Turns comparison operations to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo === $bar, "message");
+$this->assertSame($bar, $foo, "message");
```
```diff
-$this->assertFalse($foo >= $bar, "message");
+$this->assertLessThanOrEqual($bar, $foo, "message");
```

<br><br>

### `AssertEqualsParameterToSpecificMethodsTypeRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector`](/rules/phpunit/src/Rector/MethodCall/AssertEqualsParameterToSpecificMethodsTypeRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertEqualsParameterToSpecificMethodsTypeRector/Fixture)

Change `assertEquals()/assertNotEquals()` method parameters to new specific alternatives

```diff
 final class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $value = 'value';
-        $this->assertEquals('string', $value, 'message', 5.0);
+        $this->assertEqualsWithDelta('string', $value, 5.0, 'message');

-        $this->assertEquals('string', $value, 'message', 0.0, 20);
+        $this->assertEquals('string', $value, 'message', 0.0);

-        $this->assertEquals('string', $value, 'message', 0.0, 10, true);
+        $this->assertEqualsCanonicalizing('string', $value, 'message');

-        $this->assertEquals('string', $value, 'message', 0.0, 10, false, true);
+        $this->assertEqualsIgnoringCase('string', $value, 'message');
     }
 }
```

<br><br>

### `AssertEqualsToSameRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector`](/rules/phpunit/src/Rector/MethodCall/AssertEqualsToSameRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertEqualsToSameRector/Fixture)

Turns `assertEquals()` into stricter `assertSame()` for scalar values in PHPUnit TestCase

```diff
-$this->assertEquals(2, $result, "message");
+$this->assertSame(2, $result, "message");
```
```diff
-$this->assertEquals($aString, $result, "message");
+$this->assertSame($aString, $result, "message");
```

<br><br>

### `AssertFalseStrposToContainsRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector`](/rules/phpunit/src/Rector/MethodCall/AssertFalseStrposToContainsRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertFalseStrposToContainsRector/Fixture)

Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertFalse(strpos($anything, "foo"), "message");
+$this->assertNotContains("foo", $anything, "message");
```
```diff
-$this->assertNotFalse(stripos($anything, "foo"), "message");
+$this->assertContains("foo", $anything, "message");
```

<br><br>

### `AssertInstanceOfComparisonRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector`](/rules/phpunit/src/Rector/MethodCall/AssertInstanceOfComparisonRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertInstanceOfComparisonRector/Fixture)

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo instanceof Foo, "message");
+$this->assertInstanceOf("Foo", $foo, "message");
```
```diff
-$this->assertFalse($foo instanceof Foo, "message");
+$this->assertNotInstanceOf("Foo", $foo, "message");
```

<br><br>

### `AssertIssetToSpecificMethodRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertIssetToSpecificMethodRector`](/rules/phpunit/src/Rector/MethodCall/AssertIssetToSpecificMethodRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertIssetToSpecificMethodRector/Fixture)

Turns isset comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(isset($anything->foo));
+$this->assertObjectHasAttribute("foo", $anything);
```
```diff
-$this->assertFalse(isset($anything["foo"]), "message");
+$this->assertArrayNotHasKey("foo", $anything, "message");
```

<br><br>

### `AssertNotOperatorRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertNotOperatorRector`](/rules/phpunit/src/Rector/MethodCall/AssertNotOperatorRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertNotOperatorRector/Fixture)

Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(!$foo, "message");
+$this->assertFalse($foo, "message");
```
```diff
-$this->assertFalse(!$foo, "message");
+$this->assertTrue($foo, "message");
```

<br><br>

### `AssertPropertyExistsRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector`](/rules/phpunit/src/Rector/MethodCall/AssertPropertyExistsRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertPropertyExistsRector/Fixture)

Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(property_exists(new Class, "property"), "message");
+$this->assertClassHasAttribute("property", "Class", "message");
```
```diff
-$this->assertFalse(property_exists(new Class, "property"), "message");
+$this->assertClassNotHasAttribute("property", "Class", "message");
```

<br><br>

### `AssertRegExpRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertRegExpRector`](/rules/phpunit/src/Rector/MethodCall/AssertRegExpRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertRegExpRector/Fixture)

Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(1, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertRegExp("/^Message for ".*"\.$/", $string, $message);
```
```diff
-$this->assertEquals(false, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertNotRegExp("/^Message for ".*"\.$/", $string, $message);
```

<br><br>

### `AssertResourceToClosedResourceRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertResourceToClosedResourceRector`](/rules/phpunit/src/Rector/MethodCall/AssertResourceToClosedResourceRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertResourceToClosedResourceRector/Fixture)

Turns `assertIsNotResource()` into stricter `assertIsClosedResource()` for resource values in PHPUnit TestCase

```diff
-$this->assertIsNotResource($aResource, "message");
+$this->assertIsClosedResource($aResource, "message");
```

<br><br>

### `AssertSameBoolNullToSpecificMethodRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector`](/rules/phpunit/src/Rector/MethodCall/AssertSameBoolNullToSpecificMethodRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertSameBoolNullToSpecificMethodRector/Fixture)

Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(null, $anything);
+$this->assertNull($anything);
```
```diff
-$this->assertNotSame(false, $anything);
+$this->assertNotFalse($anything);
```

<br><br>

### `AssertTrueFalseInternalTypeToSpecificMethodRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector`](/rules/phpunit/src/Rector/MethodCall/AssertTrueFalseInternalTypeToSpecificMethodRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertTrueFalseInternalTypeToSpecificMethodRector/Fixture)

Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(is_{internal_type}($anything), "message");
+$this->assertInternalType({internal_type}, $anything, "message");
```
```diff
-$this->assertFalse(is_{internal_type}($anything), "message");
+$this->assertNotInternalType({internal_type}, $anything, "message");
```

<br><br>

### `AssertTrueFalseToSpecificMethodRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector`](/rules/phpunit/src/Rector/MethodCall/AssertTrueFalseToSpecificMethodRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertTrueFalseToSpecificMethodRector/Fixture)

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

<br><br>

### `CreateMockToCreateStubRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\CreateMockToCreateStubRector`](/rules/phpunit/src/Rector/MethodCall/CreateMockToCreateStubRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/CreateMockToCreateStubRector/Fixture)

Replaces `createMock()` with `createStub()` when relevant

```diff
 use PHPUnit\Framework\TestCase

 class MyTest extends TestCase
 {
     public function testItBehavesAsExpected(): void
     {
-        $stub = $this->createMock(\Exception::class);
+        $stub = $this->createStub(\Exception::class);
         $stub->method('getMessage')
             ->willReturn('a message');

         $mock = $this->createMock(\Exception::class);
         $mock->expects($this->once())
             ->method('getMessage')
             ->willReturn('a message');

         self::assertSame('a message', $stub->getMessage());
         self::assertSame('a message', $mock->getMessage());
     }
 }
```

<br><br>

### `DelegateExceptionArgumentsRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\DelegateExceptionArgumentsRector`](/rules/phpunit/src/Rector/MethodCall/DelegateExceptionArgumentsRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/DelegateExceptionArgumentsRector/Fixture)

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

```diff
-$this->setExpectedException(Exception::class, "Message", "CODE");
+$this->setExpectedException(Exception::class);
+$this->expectExceptionMessage('Message');
+$this->expectExceptionCode('CODE');
```

<br><br>

### `ExceptionAnnotationRector`

- class: [`Rector\PHPUnit\Rector\ClassMethod\ExceptionAnnotationRector`](/rules/phpunit/src/Rector/ClassMethod/ExceptionAnnotationRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/ClassMethod/ExceptionAnnotationRector/Fixture)

Changes `@expectedException annotations to expectException*() methods

```diff
-/**
- * @expectedException Exception
- * @expectedExceptionMessage Message
- */
 public function test()
 {
+    $this->expectException('Exception');
+    $this->expectExceptionMessage('Message');
     // tested code
 }
```

<br><br>

### `ExplicitPhpErrorApiRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector`](/rules/phpunit/src/Rector/MethodCall/ExplicitPhpErrorApiRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/ExplicitPhpErrorApiRector/Fixture)

Use explicit API for expecting PHP errors, warnings, and notices

```diff
 final class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
-        $this->expectException(\PHPUnit\Framework\TestCase\Deprecated::class);
-        $this->expectException(\PHPUnit\Framework\TestCase\Error::class);
-        $this->expectException(\PHPUnit\Framework\TestCase\Notice::class);
-        $this->expectException(\PHPUnit\Framework\TestCase\Warning::class);
+        $this->expectDeprecation();
+        $this->expectError();
+        $this->expectNotice();
+        $this->expectWarning();
     }
 }
```

<br><br>

### `GetMockBuilderGetMockToCreateMockRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector`](/rules/phpunit/src/Rector/MethodCall/GetMockBuilderGetMockToCreateMockRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/GetMockBuilderGetMockToCreateMockRector/Fixture)

Remove `getMockBuilder()` to `createMock()`

```diff
 class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
-        $applicationMock = $this->getMockBuilder('SomeClass')
-           ->disableOriginalConstructor()
-           ->getMock();
+        $applicationMock = $this->createMock('SomeClass');
     }
 }
```

<br><br>

### `GetMockRector`

- class: [`Rector\PHPUnit\Rector\StaticCall\GetMockRector`](/rules/phpunit/src/Rector/StaticCall/GetMockRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/StaticCall/GetMockRector/Fixture)

Turns getMock*() methods to `createMock()`

```diff
-$this->getMock("Class");
+$this->createMock("Class");
```
```diff
-$this->getMockWithoutInvokingTheOriginalConstructor("Class");
+$this->createMock("Class");
```

<br><br>

### `RemoveDataProviderTestPrefixRector`

- class: [`Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector`](/rules/phpunit/src/Rector/Class_/RemoveDataProviderTestPrefixRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/Class_/RemoveDataProviderTestPrefixRector/Fixture)

Data provider methods cannot start with "test" prefix

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
     /**
-     * @dataProvider testProvideData()
+     * @dataProvider provideData()
      */
     public function test()
     {
         $nothing = 5;
     }

-    public function testProvideData()
+    public function provideData()
     {
         return ['123'];
     }
 }
```

<br><br>

### `RemoveEmptyTestMethodRector`

- class: [`Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector`](/rules/phpunit/src/Rector/ClassMethod/RemoveEmptyTestMethodRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/ClassMethod/RemoveEmptyTestMethodRector/Fixture)

Remove empty test methods

```diff
 class SomeTest extends \PHPUnit\Framework\TestCase
 {
-    /**
-     * testGetTranslatedModelField method
-     *
-     * @return void
-     */
-    public function testGetTranslatedModelField()
-    {
-    }
 }
```

<br><br>

### `RemoveExpectAnyFromMockRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector`](/rules/phpunit/src/Rector/MethodCall/RemoveExpectAnyFromMockRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/RemoveExpectAnyFromMockRector/Fixture)

Remove `expect($this->any())` from mocks as it has no added value

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
 {
     public function test()
     {
         $translator = $this->getMock('SomeClass');
-        $translator->expects($this->any())
-            ->method('trans')
+        $translator->method('trans')
             ->willReturn('translated max {{ max }}!');
     }
 }
```

<br><br>

### `ReplaceAssertArraySubsetRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetRector`](/rules/phpunit/src/Rector/MethodCall/ReplaceAssertArraySubsetRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/ReplaceAssertArraySubsetRector/Fixture)

Replace deprecated "assertArraySubset()" method with alternative methods

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     public function test()
     {
         $checkedArray = [];

-        $this->assertArraySubset([
-           'cache_directory' => 'new_value',
-        ], $checkedArray, true);
+        $this->assertArrayHasKey('cache_directory', $checkedArray);
+        $this->assertSame('new_value', $checkedArray['cache_directory']);
     }
 }
```

<br><br>

### `ReplaceAssertArraySubsetWithDmsPolyfillRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector`](/rules/phpunit/src/Rector/MethodCall/ReplaceAssertArraySubsetWithDmsPolyfillRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/ReplaceAssertArraySubsetWithDmsPolyfillRector/Fixture)

Change `assertArraySubset()` to static call of DMS\PHPUnitExtensions\ArraySubset\Assert

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
 {
     public function test()
     {
-        self::assertArraySubset(['bar' => 0], ['bar' => '0'], true);
+        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);

-        $this->assertArraySubset(['bar' => 0], ['bar' => '0'], true);
+        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);
     }
 }
```

<br><br>

### `SelfContainerGetMethodCallFromTestToInjectPropertyRector`

- class: [`Rector\PHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector`](/rules/phpunit/src/Rector/Class_/SelfContainerGetMethodCallFromTestToInjectPropertyRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/Class_/SelfContainerGetMethodCallFromTestToInjectPropertyRector/Fixture)

Change `$container->get()` calls in PHPUnit to @inject properties autowired by jakzal/phpunit-injector

```diff
 use PHPUnit\Framework\TestCase;
 class SomeClassTest extends TestCase {
+    /**
+     * @var SomeService
+     * @inject
+     */
+    private $someService;
     public function test()
     {
-        $someService = $this->getContainer()->get(SomeService::class);
+        $someService = $this->someService;
     }
 }

 class SomeService
 {
 }
```

<br><br>

### `SimplifyForeachInstanceOfRector`

- class: [`Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector`](/rules/phpunit/src/Rector/Foreach_/SimplifyForeachInstanceOfRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/Foreach_/SimplifyForeachInstanceOfRector/Fixture)

Simplify unnecessary foreach check of instances

```diff
-foreach ($foos as $foo) {
-    $this->assertInstanceOf(SplFileInfo::class, $foo);
-}
+$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
```

<br><br>

### `SpecificAssertContainsRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector`](/rules/phpunit/src/Rector/MethodCall/SpecificAssertContainsRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/SpecificAssertContainsRector/Fixture)

Change `assertContains()/assertNotContains()` method to new string and iterable alternatives

```diff
 <?php

 final class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
-        $this->assertContains('foo', 'foo bar');
-        $this->assertNotContains('foo', 'foo bar');
+        $this->assertStringContainsString('foo', 'foo bar');
+        $this->assertStringNotContainsString('foo', 'foo bar');
     }
 }
```

<br><br>

### `SpecificAssertContainsWithoutIdentityRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector`](/rules/phpunit/src/Rector/MethodCall/SpecificAssertContainsWithoutIdentityRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/SpecificAssertContainsWithoutIdentityRector/Fixture)

Change `assertContains()/assertNotContains()` with non-strict comparison to new specific alternatives

```diff
 <?php

-final class SomeTest extends \PHPUnit\Framework\TestCase
+final class SomeTest extends TestCase
 {
     public function test()
     {
         $objects = [ new \stdClass(), new \stdClass(), new \stdClass() ];
-        $this->assertContains(new \stdClass(), $objects, 'message', false, false);
-        $this->assertNotContains(new \stdClass(), $objects, 'message', false, false);
+        $this->assertContainsEquals(new \stdClass(), $objects, 'message');
+        $this->assertNotContainsEquals(new \stdClass(), $objects, 'message');
     }
 }
```

<br><br>

### `SpecificAssertInternalTypeRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector`](/rules/phpunit/src/Rector/MethodCall/SpecificAssertInternalTypeRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/SpecificAssertInternalTypeRector/Fixture)

Change `assertInternalType()/assertNotInternalType()` method to new specific alternatives

```diff
 final class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $value = 'value';
-        $this->assertInternalType('string', $value);
-        $this->assertNotInternalType('array', $value);
+        $this->assertIsString($value);
+        $this->assertIsNotArray($value);
     }
 }
```

<br><br>

### `TestListenerToHooksRector`

- class: [`Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector`](/rules/phpunit/src/Rector/Class_/TestListenerToHooksRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/Class_/TestListenerToHooksRector/Fixture)

Refactor "*TestListener.php" to particular "*Hook.php" files

```diff
 namespace App\Tests;

-use PHPUnit\Framework\TestListener;
-
-final class BeforeListHook implements TestListener
+final class BeforeListHook implements \PHPUnit\Runner\BeforeTestHook, \PHPUnit\Runner\AfterTestHook
 {
-    public function addError(Test $test, \Throwable $t, float $time): void
+    public function executeBeforeTest(Test $test): void
     {
-    }
-
-    public function addWarning(Test $test, Warning $e, float $time): void
-    {
-    }
-
-    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
-    {
-    }
-
-    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
-    {
-    }
-
-    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
-    {
-    }
-
-    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
-    {
-    }
-
-    public function startTestSuite(TestSuite $suite): void
-    {
-    }
-
-    public function endTestSuite(TestSuite $suite): void
-    {
-    }
-
-    public function startTest(Test $test): void
-    {
         echo 'start test!';
     }

-    public function endTest(Test $test, float $time): void
+    public function executeAfterTest(Test $test, float $time): void
     {
         echo $time;
     }
 }
```

<br><br>

### `TryCatchToExpectExceptionRector`

- class: [`Rector\PHPUnit\Rector\ClassMethod\TryCatchToExpectExceptionRector`](/rules/phpunit/src/Rector/ClassMethod/TryCatchToExpectExceptionRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/ClassMethod/TryCatchToExpectExceptionRector/Fixture)

Turns try/catch to `expectException()` call

```diff
-try {
-	$someService->run();
-} catch (Throwable $exception) {
-    $this->assertInstanceOf(RuntimeException::class, $e);
-    $this->assertContains('There was an error executing the following script', $e->getMessage());
-}
+$this->expectException(RuntimeException::class);
+$this->expectExceptionMessage('There was an error executing the following script');
+$someService->run();
```

<br><br>

### `UseSpecificWillMethodRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector`](/rules/phpunit/src/Rector/MethodCall/UseSpecificWillMethodRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/UseSpecificWillMethodRector/Fixture)

Changes ->will($this->xxx()) to one specific method

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
         $translator->expects($this->any())
             ->method('trans')
-            ->with($this->equalTo('old max {{ max }}!'))
-            ->will($this->returnValue('translated max {{ max }}!'));
+            ->with('old max {{ max }}!')
+            ->willReturnValue('translated max {{ max }}!');
     }
 }
```

<br><br>

### `WithConsecutiveArgToArrayRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\WithConsecutiveArgToArrayRector`](/rules/phpunit/src/Rector/MethodCall/WithConsecutiveArgToArrayRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/WithConsecutiveArgToArrayRector/Fixture)

Split `withConsecutive()` arg to array

```diff
 class SomeClass
 {
     public function run($one, $two)
     {
     }
 }

 class SomeTestCase extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $someClassMock = $this->createMock(SomeClass::class);
         $someClassMock
             ->expects($this->exactly(2))
             ->method('run')
-            ->withConsecutive(1, 2, 3, 5);
+            ->withConsecutive([1, 2], [3, 5]);
     }
 }
```

<br><br>

## PHPUnitSymfony

### `AddMessageToEqualsResponseCodeRector`

- class: [`Rector\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector`](/rules/phpunit-symfony/src/Rector/StaticCall/AddMessageToEqualsResponseCodeRector.php)
- [test fixtures](/rules/phpunit-symfony/tests/Rector/StaticCall/AddMessageToEqualsResponseCodeRector/Fixture)

Add response content to response code assert, so it is easier to debug

```diff
 use PHPUnit\Framework\TestCase;
 use Symfony\Component\HttpFoundation\Response;

 final class SomeClassTest extends TestCase
 {
     public function test(Response $response)
     {
         $this->assertEquals(
             Response::HTTP_NO_CONTENT,
             $response->getStatusCode()
+            $response->getContent()
         );
     }
 }
```

<br><br>

## PSR4

### `MultipleClassFileToPsr4ClassesRector`

- class: [`Rector\PSR4\Rector\MultipleClassFileToPsr4ClassesRector`](/rules/psr4/src/Rector/MultipleClassFileToPsr4ClassesRector.php)

Turns namespaced classes in one file to standalone PSR-4 classes.

```diff
+// new file: "app/Exceptions/FirstException.php"
 namespace App\Exceptions;

 use Exception;

 final class FirstException extends Exception
 {

 }
+
+// new file: "app/Exceptions/SecondException.php"
+namespace App\Exceptions;
+
+use Exception;

 final class SecondException extends Exception
 {

 }
```

<br><br>

### `NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector`

- class: [`Rector\PSR4\Rector\FileSystem\NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector`](/rules/psr4/src/Rector/FileSystem/NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector.php)
- [test fixtures](/rules/psr4/tests/Rector/FileSystem/NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector/Fixture)

Adds namespace to namespace-less files to match PSR-4 in `composer.json` autoload section. Run with combination with `Rector\PSR4\Rector\MultipleClassFileToPsr4ClassesRector`

```diff
 // src/SomeClass.php

+namespace App\CustomNamespace;
+
 class SomeClass
 {
 }
```

composer.json
```json
{
    "autoload": {
        "psr-4": {
            "App\\CustomNamespace\\": "src"
        }
    }
}
```


<br><br>

### `NormalizeNamespaceByPSR4ComposerAutoloadRector`

- class: [`Rector\PSR4\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector`](/rules/psr4/src/Rector/Namespace_/NormalizeNamespaceByPSR4ComposerAutoloadRector.php)
- [test fixtures](/rules/psr4/tests/Rector/Namespace_/NormalizeNamespaceByPSR4ComposerAutoloadRector/Fixture)

Changes namespace and class names to match PSR-4 in `composer.json` autoload section

```diff
 // src/SomeClass.php

-namespace App\DifferentNamespace;
+namespace App\CustomNamespace;

 class SomeClass
 {
 }
```

composer.json
```json
{
    "autoload": {
        "psr-4": {
            "App\\CustomNamespace\\": "src"
        }
    }
}
```


<br><br>

## Performance

### `PreslashSimpleFunctionRector`

- class: [`Rector\Performance\Rector\FuncCall\PreslashSimpleFunctionRector`](/rules/performance/src/Rector/FuncCall/PreslashSimpleFunctionRector.php)
- [test fixtures](/rules/performance/tests/Rector/FuncCall/PreslashSimpleFunctionRector/Fixture)

Add pre-slash to short named functions to improve performance

```diff
 class SomeClass
 {
     public function shorten($value)
     {
-        return trim($value);
+        return \trim($value);
     }
 }
```

<br><br>

## Phalcon

### `AddRequestToHandleMethodCallRector`

- class: [`Rector\Phalcon\Rector\MethodCall\AddRequestToHandleMethodCallRector`](/rules/phalcon/src/Rector/MethodCall/AddRequestToHandleMethodCallRector.php)
- [test fixtures](/rules/phalcon/tests/Rector/MethodCall/AddRequestToHandleMethodCallRector/Fixture)

Add $_SERVER REQUEST_URI to method call

```diff
 class SomeClass
 {
     public function run($di)
     {
         $application = new \Phalcon\Mvc\Application();
-        $response = $application->handle();
+        $response = $application->handle($_SERVER["REQUEST_URI"]);
     }
 }
```

<br><br>

### `DecoupleSaveMethodCallWithArgumentToAssignRector`

- class: [`Rector\Phalcon\Rector\MethodCall\DecoupleSaveMethodCallWithArgumentToAssignRector`](/rules/phalcon/src/Rector/MethodCall/DecoupleSaveMethodCallWithArgumentToAssignRector.php)
- [test fixtures](/rules/phalcon/tests/Rector/MethodCall/DecoupleSaveMethodCallWithArgumentToAssignRector/Fixture)

Decouple `Phalcon\Mvc\Model::save()` with argument to `assign()`

```diff
 class SomeClass
 {
     public function run(\Phalcon\Mvc\Model $model, $data)
     {
-        $model->save($data);
+        $model->save();
+        $model->assign($data);
     }
 }
```

<br><br>

### `FlashWithCssClassesToExtraCallRector`

- class: [`Rector\Phalcon\Rector\Assign\FlashWithCssClassesToExtraCallRector`](/rules/phalcon/src/Rector/Assign/FlashWithCssClassesToExtraCallRector.php)
- [test fixtures](/rules/phalcon/tests/Rector/Assign/FlashWithCssClassesToExtraCallRector/Fixture)

Add `$cssClasses` in Flash to separated method call

```diff
 class SomeClass
 {
     public function run()
     {
         $cssClasses = [];
-        $flash = new Phalcon\Flash($cssClasses);
+        $flash = new Phalcon\Flash();
+        $flash->setCssClasses($cssClasses);
     }
 }
```

<br><br>

### `NewApplicationToToFactoryWithDefaultContainerRector`

- class: [`Rector\Phalcon\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector`](/rules/phalcon/src/Rector/Assign/NewApplicationToToFactoryWithDefaultContainerRector.php)
- [test fixtures](/rules/phalcon/tests/Rector/Assign/NewApplicationToToFactoryWithDefaultContainerRector/Fixture)

Change new application to default factory with application

```diff
 class SomeClass
 {
     public function run($di)
     {
-        $application = new \Phalcon\Mvc\Application($di);
+        $container = new \Phalcon\Di\FactoryDefault();
+        $application = new \Phalcon\Mvc\Application($container);

-        $response = $application->handle();
+        $response = $application->handle($_SERVER["REQUEST_URI"]);
     }
 }
```

<br><br>

## Php52

### `ContinueToBreakInSwitchRector`

- class: [`Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector`](/rules/php52/src/Rector/Switch_/ContinueToBreakInSwitchRector.php)
- [test fixtures](/rules/php52/tests/Rector/Switch_/ContinueToBreakInSwitchRector/Fixture)

Use break instead of continue in switch statements

```diff
 function some_run($value)
 {
     switch ($value) {
         case 1:
             echo 'Hi';
-            continue;
+            break;
         case 2:
             echo 'Hello';
             break;
     }
 }
```

<br><br>

### `VarToPublicPropertyRector`

- class: [`Rector\Php52\Rector\Property\VarToPublicPropertyRector`](/rules/php52/src/Rector/Property/VarToPublicPropertyRector.php)
- [test fixtures](/rules/php52/tests/Rector/Property/VarToPublicPropertyRector/Fixture)

Remove unused private method

```diff
 final class SomeController
 {
-    var $name = 'Tom';
+    public $name = 'Tom';
 }
```

<br><br>

## Php53

### `ClearReturnNewByReferenceRector`

- class: [`Rector\Php53\Rector\AssignRef\ClearReturnNewByReferenceRector`](/rules/php53/src/Rector/AssignRef/ClearReturnNewByReferenceRector.php)
- [test fixtures](/rules/php53/tests/Rector/AssignRef/ClearReturnNewByReferenceRector/Fixture)

Remove reference from "$assign = &new Value;"

```diff
-$assign = &new Value;
+$assign = new Value;
```

<br><br>

### `DirNameFileConstantToDirConstantRector`

- class: [`Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector`](/rules/php53/src/Rector/FuncCall/DirNameFileConstantToDirConstantRector.php)
- [test fixtures](/rules/php53/tests/Rector/FuncCall/DirNameFileConstantToDirConstantRector/Fixture)

Convert dirname(__FILE__) to __DIR__

```diff
 class SomeClass
 {
     public function run()
     {
-        return dirname(__FILE__);
+        return __DIR__;
     }
 }
```

<br><br>

### `ReplaceHttpServerVarsByServerRector`

- class: [`Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector`](/rules/php53/src/Rector/Variable/ReplaceHttpServerVarsByServerRector.php)
- [test fixtures](/rules/php53/tests/Rector/Variable/ReplaceHttpServerVarsByServerRector/Fixture)

Rename old `$HTTP_*` variable names to new replacements

```diff
-$serverVars = $HTTP_SERVER_VARS;
+$serverVars = $_SERVER;
```

<br><br>

### `TernaryToElvisRector`

- class: [`Rector\Php53\Rector\Ternary\TernaryToElvisRector`](/rules/php53/src/Rector/Ternary/TernaryToElvisRector.php)
- [test fixtures](/rules/php53/tests/Rector/Ternary/TernaryToElvisRector/Fixture)

Use ?: instead of ?, where useful

```diff
 function elvis()
 {
-    $value = $a ? $a : false;
+    $value = $a ?: false;
 }
```

<br><br>

## Php54

### `RemoveReferenceFromCallRector`

- class: [`Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector`](/rules/php54/src/Rector/FuncCall/RemoveReferenceFromCallRector.php)
- [test fixtures](/rules/php54/tests/Rector/FuncCall/RemoveReferenceFromCallRector/Fixture)

Remove & from function and method calls

```diff
 final class SomeClass
 {
     public function run($one)
     {
-        return strlen(&$one);
+        return strlen($one);
     }
 }
```

<br><br>

### `RemoveZeroBreakContinueRector`

- class: [`Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector`](/rules/php54/src/Rector/Break_/RemoveZeroBreakContinueRector.php)
- [test fixtures](/rules/php54/tests/Rector/Break_/RemoveZeroBreakContinueRector/Fixture)

Remove 0 from break and continue

```diff
 class SomeClass
 {
     public function run($random)
     {
-        continue 0;
-        break 0;
+        continue;
+        break;

         $five = 5;
-        continue $five;
+        continue 5;

-        break $random;
+        break;
     }
 }
```

<br><br>

## Php55

### `PregReplaceEModifierRector`

- class: [`Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector`](/rules/php55/src/Rector/FuncCall/PregReplaceEModifierRector.php)
- [test fixtures](/rules/php55/tests/Rector/FuncCall/PregReplaceEModifierRector/Fixture)

The /e modifier is no longer supported, use `preg_replace_callback` instead

```diff
 class SomeClass
 {
     public function run()
     {
-        $comment = preg_replace('~\b(\w)(\w+)~e', '"$1".strtolower("$2")', $comment);
+        $comment = preg_replace_callback('~\b(\w)(\w+)~', function ($matches) {
+              return($matches[1].strtolower($matches[2]));
+        }, , $comment);
     }
 }
```

<br><br>

### `StringClassNameToClassConstantRector`

- class: [`Rector\Php55\Rector\String_\StringClassNameToClassConstantRector`](/rules/php55/src/Rector/String_/StringClassNameToClassConstantRector.php)
- [test fixtures](/rules/php55/tests/Rector/String_/StringClassNameToClassConstantRector/Fixture)

Replace string class names by <class>::class constant

```php
<?php

declare(strict_types=1);

use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringClassNameToClassConstantRector::class)
        ->call(
            'configure',
            [[
                StringClassNameToClassConstantRector::CLASSES_TO_SKIP => ['ClassName', 'AnotherClassName'],
            ]]
        );
};
```

↓

```diff
 class AnotherClass
 {
 }

 class SomeClass
 {
     public function run()
     {
-        return 'AnotherClass';
+        return \AnotherClass::class;
     }
 }
```

<br><br>

## Php56

### `AddDefaultValueForUndefinedVariableRector`

- class: [`Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector`](/rules/php56/src/Rector/FunctionLike/AddDefaultValueForUndefinedVariableRector.php)
- [test fixtures](/rules/php56/tests/Rector/FunctionLike/AddDefaultValueForUndefinedVariableRector/Fixture)

Adds default value for undefined variable

```diff
 class SomeClass
 {
     public function run()
     {
+        $a = null;
         if (rand(0, 1)) {
             $a = 5;
         }
         echo $a;
     }
 }
```

<br><br>

### `PowToExpRector`

- class: [`Rector\Php56\Rector\FuncCall\PowToExpRector`](/rules/php56/src/Rector/FuncCall/PowToExpRector.php)
- [test fixtures](/rules/php56/tests/Rector/FuncCall/PowToExpRector/Fixture)

Changes pow(val, val2) to ** `(exp)` parameter

```diff
-pow(1, 2);
+1**2;
```

<br><br>

## Php70

### `BreakNotInLoopOrSwitchToReturnRector`

- class: [`Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector`](/rules/php70/src/Rector/Break_/BreakNotInLoopOrSwitchToReturnRector.php)
- [test fixtures](/rules/php70/tests/Rector/Break_/BreakNotInLoopOrSwitchToReturnRector/Fixture)

Convert break outside for/foreach/switch context to return

```diff
 class SomeClass
 {
     public function run()
     {
         if ($isphp5)
             return 1;
         else
             return 2;
-        break;
+        return;
     }
 }
```

<br><br>

### `CallUserMethodRector`

- class: [`Rector\Php70\Rector\FuncCall\CallUserMethodRector`](/rules/php70/src/Rector/FuncCall/CallUserMethodRector.php)
- [test fixtures](/rules/php70/tests/Rector/FuncCall/CallUserMethodRector/Fixture)

Changes `call_user_method()/call_user_method_array()` to `call_user_func()/call_user_func_array()`

```diff
-call_user_method($method, $obj, "arg1", "arg2");
+call_user_func(array(&$obj, "method"), "arg1", "arg2");
```

<br><br>

### `EmptyListRector`

- class: [`Rector\Php70\Rector\List_\EmptyListRector`](/rules/php70/src/Rector/List_/EmptyListRector.php)
- [test fixtures](/rules/php70/tests/Rector/List_/EmptyListRector/Fixture)

`list()` cannot be empty

```diff
-'list() = $values;'
+'list($unusedGenerated) = $values;'
```

<br><br>

### `EregToPregMatchRector`

- class: [`Rector\Php70\Rector\FuncCall\EregToPregMatchRector`](/rules/php70/src/Rector/FuncCall/EregToPregMatchRector.php)
- [test fixtures](/rules/php70/tests/Rector/FuncCall/EregToPregMatchRector/Fixture)

Changes ereg*() to preg*() calls

```diff
-ereg("hi")
+preg_match("#hi#");
```

<br><br>

### `ExceptionHandlerTypehintRector`

- class: [`Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector`](/rules/php70/src/Rector/FunctionLike/ExceptionHandlerTypehintRector.php)
- [test fixtures](/rules/php70/tests/Rector/FunctionLike/ExceptionHandlerTypehintRector/Fixture)

Changes property `@var` annotations from annotation to type.

```diff
-function handler(Exception $exception) { ... }
+function handler(Throwable $exception) { ... }
 set_exception_handler('handler');
```

<br><br>

### `IfToSpaceshipRector`

- class: [`Rector\Php70\Rector\If_\IfToSpaceshipRector`](/rules/php70/src/Rector/If_/IfToSpaceshipRector.php)
- [test fixtures](/rules/php70/tests/Rector/If_/IfToSpaceshipRector/Fixture)

Changes if/else to spaceship <=> where useful

```diff
 class SomeClass
 {
     public function run()
     {
         usort($languages, function ($a, $b) {
-            if ($a[0] === $b[0]) {
-                return 0;
-            }
-
-            return ($a[0] < $b[0]) ? 1 : -1;
+            return $b[0] <=> $a[0];
         });
     }
 }
```

<br><br>

### `ListSplitStringRector`

- class: [`Rector\Php70\Rector\Assign\ListSplitStringRector`](/rules/php70/src/Rector/Assign/ListSplitStringRector.php)
- [test fixtures](/rules/php70/tests/Rector/Assign/ListSplitStringRector/Fixture)

`list()` cannot split string directly anymore, use `str_split()`

```diff
-list($foo) = "string";
+list($foo) = str_split("string");
```

<br><br>

### `ListSwapArrayOrderRector`

- class: [`Rector\Php70\Rector\Assign\ListSwapArrayOrderRector`](/rules/php70/src/Rector/Assign/ListSwapArrayOrderRector.php)
- [test fixtures](/rules/php70/tests/Rector/Assign/ListSwapArrayOrderRector/Fixture)

`list()` assigns variables in reverse order - relevant in array assign

```diff
-list($a[], $a[]) = [1, 2];
+list($a[], $a[]) = array_reverse([1, 2]);
```

<br><br>

### `MultiDirnameRector`

- class: [`Rector\Php70\Rector\FuncCall\MultiDirnameRector`](/rules/php70/src/Rector/FuncCall/MultiDirnameRector.php)
- [test fixtures](/rules/php70/tests/Rector/FuncCall/MultiDirnameRector/Fixture)

Changes multiple `dirname()` calls to one with nesting level

```diff
-dirname(dirname($path));
+dirname($path, 2);
```

<br><br>

### `NonVariableToVariableOnFunctionCallRector`

- class: [`Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector`](/rules/php70/src/Rector/FuncCall/NonVariableToVariableOnFunctionCallRector.php)
- [test fixtures](/rules/php70/tests/Rector/FuncCall/NonVariableToVariableOnFunctionCallRector/Fixture)

Transform non variable like arguments to variable where a function or method expects an argument passed by reference

```diff
-reset(a());
+$a = a(); reset($a);
```

<br><br>

### `Php4ConstructorRector`

- class: [`Rector\Php70\Rector\ClassMethod\Php4ConstructorRector`](/rules/php70/src/Rector/ClassMethod/Php4ConstructorRector.php)
- [test fixtures](/rules/php70/tests/Rector/ClassMethod/Php4ConstructorRector/Fixture)

Changes PHP 4 style constructor to __construct.

```diff
 class SomeClass
 {
-    public function SomeClass()
+    public function __construct()
     {
     }
 }
```

<br><br>

### `RandomFunctionRector`

- class: [`Rector\Php70\Rector\FuncCall\RandomFunctionRector`](/rules/php70/src/Rector/FuncCall/RandomFunctionRector.php)
- [test fixtures](/rules/php70/tests/Rector/FuncCall/RandomFunctionRector/Fixture)

Changes rand, `srand` and `getrandmax` by new mt_* alternatives.

```diff
-rand();
+mt_rand();
```

<br><br>

### `ReduceMultipleDefaultSwitchRector`

- class: [`Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector`](/rules/php70/src/Rector/Switch_/ReduceMultipleDefaultSwitchRector.php)
- [test fixtures](/rules/php70/tests/Rector/Switch_/ReduceMultipleDefaultSwitchRector/Fixture)

Remove first default switch, that is ignored

```diff
 switch ($expr) {
     default:
-         echo "Hello World";
-
-    default:
          echo "Goodbye Moon!";
          break;
 }
```

<br><br>

### `RenameMktimeWithoutArgsToTimeRector`

- class: [`Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector`](/rules/php70/src/Rector/FuncCall/RenameMktimeWithoutArgsToTimeRector.php)
- [test fixtures](/rules/php70/tests/Rector/FuncCall/RenameMktimeWithoutArgsToTimeRector/Fixture)

Renames `mktime()` without arguments to `time()`

```diff
 class SomeClass
 {
     public function run()
     {
         $time = mktime(1, 2, 3);
-        $nextTime = mktime();
+        $nextTime = time();
     }
 }
```

<br><br>

### `StaticCallOnNonStaticToInstanceCallRector`

- class: [`Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector`](/rules/php70/src/Rector/StaticCall/StaticCallOnNonStaticToInstanceCallRector.php)
- [test fixtures](/rules/php70/tests/Rector/StaticCall/StaticCallOnNonStaticToInstanceCallRector/Fixture)

Changes static call to instance call, where not useful

```diff
 class Something
 {
     public function doWork()
     {
     }
 }

 class Another
 {
     public function run()
     {
-        return Something::doWork();
+        return (new Something)->doWork();
     }
 }
```

<br><br>

### `TernaryToNullCoalescingRector`

- class: [`Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector`](/rules/php70/src/Rector/Ternary/TernaryToNullCoalescingRector.php)
- [test fixtures](/rules/php70/tests/Rector/Ternary/TernaryToNullCoalescingRector/Fixture)

Changes unneeded null check to ?? operator

```diff
-$value === null ? 10 : $value;
+$value ?? 10;
```
```diff
-isset($value) ? $value : 10;
+$value ?? 10;
```

<br><br>

### `TernaryToSpaceshipRector`

- class: [`Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector`](/rules/php70/src/Rector/Ternary/TernaryToSpaceshipRector.php)
- [test fixtures](/rules/php70/tests/Rector/Ternary/TernaryToSpaceshipRector/Fixture)

Use <=> spaceship instead of ternary with same effect

```diff
 function order_func($a, $b) {
-    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
+    return $a <=> $b;
 }
```

<br><br>

### `ThisCallOnStaticMethodToStaticCallRector`

- class: [`Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector`](/rules/php70/src/Rector/MethodCall/ThisCallOnStaticMethodToStaticCallRector.php)
- [test fixtures](/rules/php70/tests/Rector/MethodCall/ThisCallOnStaticMethodToStaticCallRector/Fixture)

Changes `$this->call()` to static method to static call

```diff
 class SomeClass
 {
     public static function run()
     {
-        $this->eat();
+        static::eat();
     }

     public static function eat()
     {
     }
 }
```

<br><br>

### `WrapVariableVariableNameInCurlyBracesRector`

- class: [`Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector`](/rules/php70/src/Rector/Variable/WrapVariableVariableNameInCurlyBracesRector.php)
- [test fixtures](/rules/php70/tests/Rector/Variable/WrapVariableVariableNameInCurlyBracesRector/Fixture)

Ensure variable variables are wrapped in curly braces

```diff
 function run($foo)
 {
-    global $$foo->bar;
+    global ${$foo->bar};
 }
```

<br><br>

## Php71

### `AssignArrayToStringRector`

- class: [`Rector\Php71\Rector\Assign\AssignArrayToStringRector`](/rules/php71/src/Rector/Assign/AssignArrayToStringRector.php)
- [test fixtures](/rules/php71/tests/Rector/Assign/AssignArrayToStringRector/Fixture)

String cannot be turned into array by assignment anymore

```diff
-$string = '';
+$string = [];
 $string[] = 1;
```

<br><br>

### `BinaryOpBetweenNumberAndStringRector`

- class: [`Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector`](/rules/php71/src/Rector/BinaryOp/BinaryOpBetweenNumberAndStringRector.php)
- [test fixtures](/rules/php71/tests/Rector/BinaryOp/BinaryOpBetweenNumberAndStringRector/Fixture)

Change binary operation between some number + string to PHP 7.1 compatible version

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = 5 + '';
-        $value = 5.0 + 'hi';
+        $value = 5 + 0;
+        $value = 5.0 + 0
     }
 }
```

<br><br>

### `CountOnNullRector`

- class: [`Rector\Php71\Rector\FuncCall\CountOnNullRector`](/rules/php71/src/Rector/FuncCall/CountOnNullRector.php)
- [test fixtures](/rules/php71/tests/Rector/FuncCall/CountOnNullRector/Fixture)

Changes `count()` on null to safe ternary check

```diff
 $values = null;
-$count = count($values);
+$count = is_array($values) || $values instanceof Countable ? count($values) : 0;
```

<br><br>

### `IsIterableRector`

- class: [`Rector\Php71\Rector\BinaryOp\IsIterableRector`](/rules/php71/src/Rector/BinaryOp/IsIterableRector.php)
- [test fixtures](/rules/php71/tests/Rector/BinaryOp/IsIterableRector/Fixture)

Changes `is_array` + Traversable check to `is_iterable`

```diff
-is_array($foo) || $foo instanceof Traversable;
+is_iterable($foo);
```

<br><br>

### `ListToArrayDestructRector`

- class: [`Rector\Php71\Rector\List_\ListToArrayDestructRector`](/rules/php71/src/Rector/List_/ListToArrayDestructRector.php)
- [test fixtures](/rules/php71/tests/Rector/List_/ListToArrayDestructRector/Fixture)

Remove & from new &X

```diff
 class SomeClass
 {
     public function run()
     {
-        list($id1, $name1) = $data;
+        [$id1, $name1] = $data;

-        foreach ($data as list($id, $name)) {
+        foreach ($data as [$id, $name]) {
         }
     }
 }
```

<br><br>

### `MultiExceptionCatchRector`

- class: [`Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector`](/rules/php71/src/Rector/TryCatch/MultiExceptionCatchRector.php)
- [test fixtures](/rules/php71/tests/Rector/TryCatch/MultiExceptionCatchRector/Fixture)

Changes multi catch of same exception to single one | separated.

```diff
 try {
-    // Some code...
-} catch (ExceptionType1 $exception) {
-    $sameCode;
-} catch (ExceptionType2 $exception) {
-    $sameCode;
+   // Some code...
+} catch (ExceptionType1 | ExceptionType2 $exception) {
+   $sameCode;
 }
```

<br><br>

### `PublicConstantVisibilityRector`

- class: [`Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector`](/rules/php71/src/Rector/ClassConst/PublicConstantVisibilityRector.php)
- [test fixtures](/rules/php71/tests/Rector/ClassConst/PublicConstantVisibilityRector/Fixture)

Add explicit public constant visibility.

```diff
 class SomeClass
 {
-    const HEY = 'you';
+    public const HEY = 'you';
 }
```

<br><br>

### `RemoveExtraParametersRector`

- class: [`Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector`](/rules/php71/src/Rector/FuncCall/RemoveExtraParametersRector.php)
- [test fixtures](/rules/php71/tests/Rector/FuncCall/RemoveExtraParametersRector/Fixture)

Remove extra parameters

```diff
-strlen("asdf", 1);
+strlen("asdf");
```

<br><br>

### `ReservedObjectRector`

- class: [`Rector\Php71\Rector\Name\ReservedObjectRector`](/rules/php71/src/Rector/Name/ReservedObjectRector.php)
- [test fixtures](/rules/php71/tests/Rector/Name/ReservedObjectRector/Fixture)

Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured

```php
<?php

declare(strict_types=1);

use Rector\Php71\Rector\Name\ReservedObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReservedObjectRector::class)
        ->call(
            'configure',
            [[
                ReservedObjectRector::RESERVED_KEYWORDS_TO_REPLACEMENTS => [
                    'ReservedObject' => 'SmartObject',
                    'Object' => 'AnotherSmartObject',
                ],
            ]]
        );
};
```

↓

```diff
-class Object
+class SmartObject
 {
 }
```

<br><br>

## Php72

### `BarewordStringRector`

- class: [`Rector\Php72\Rector\ConstFetch\BarewordStringRector`](/rules/php72/src/Rector/ConstFetch/BarewordStringRector.php)
- [test fixtures](/rules/php72/tests/Rector/ConstFetch/BarewordStringRector/Fixture)

Changes unquoted non-existing constants to strings

```diff
-var_dump(VAR);
+var_dump("VAR");
```

<br><br>

### `CreateFunctionToAnonymousFunctionRector`

- class: [`Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector`](/rules/php72/src/Rector/FuncCall/CreateFunctionToAnonymousFunctionRector.php)
- [test fixtures](/rules/php72/tests/Rector/FuncCall/CreateFunctionToAnonymousFunctionRector/Fixture)

Use anonymous functions instead of deprecated `create_function()`

```diff
 class ClassWithCreateFunction
 {
     public function run()
     {
-        $callable = create_function('$matches', "return '$delimiter' . strtolower(\$matches[1]);");
+        $callable = function($matches) use ($delimiter) {
+            return $delimiter . strtolower($matches[1]);
+        };
     }
 }
```

<br><br>

### `GetClassOnNullRector`

- class: [`Rector\Php72\Rector\FuncCall\GetClassOnNullRector`](/rules/php72/src/Rector/FuncCall/GetClassOnNullRector.php)
- [test fixtures](/rules/php72/tests/Rector/FuncCall/GetClassOnNullRector/Fixture)

Null is no more allowed in `get_class()`

```diff
 final class SomeClass
 {
     public function getItem()
     {
         $value = null;
-        return get_class($value);
+        return $value !== null ? get_class($value) : self::class;
     }
 }
```

<br><br>

### `IsObjectOnIncompleteClassRector`

- class: [`Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector`](/rules/php72/src/Rector/FuncCall/IsObjectOnIncompleteClassRector.php)
- [test fixtures](/rules/php72/tests/Rector/FuncCall/IsObjectOnIncompleteClassRector/Fixture)

Incomplete class returns inverted bool on `is_object()`

```diff
 $incompleteObject = new __PHP_Incomplete_Class;
-$isObject = is_object($incompleteObject);
+$isObject = ! is_object($incompleteObject);
```

<br><br>

### `ListEachRector`

- class: [`Rector\Php72\Rector\Assign\ListEachRector`](/rules/php72/src/Rector/Assign/ListEachRector.php)
- [test fixtures](/rules/php72/tests/Rector/Assign/ListEachRector/Fixture)

`each()` function is deprecated, use `key()` and `current()` instead

```diff
-list($key, $callback) = each($callbacks);
+$key = key($callbacks);
+$callback = current($callbacks);
+next($callbacks);
```

<br><br>

### `ParseStrWithResultArgumentRector`

- class: [`Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector`](/rules/php72/src/Rector/FuncCall/ParseStrWithResultArgumentRector.php)
- [test fixtures](/rules/php72/tests/Rector/FuncCall/ParseStrWithResultArgumentRector/Fixture)

Use `$result` argument in `parse_str()` function

```diff
-parse_str($this->query);
-$data = get_defined_vars();
+parse_str($this->query, $result);
+$data = $result;
```

<br><br>

### `ReplaceEachAssignmentWithKeyCurrentRector`

- class: [`Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector`](/rules/php72/src/Rector/Assign/ReplaceEachAssignmentWithKeyCurrentRector.php)
- [test fixtures](/rules/php72/tests/Rector/Assign/ReplaceEachAssignmentWithKeyCurrentRector/Fixture)

Replace `each()` assign outside loop

```diff
 $array = ['b' => 1, 'a' => 2];
-$eachedArray = each($array);
+$eachedArray[1] = current($array);
+$eachedArray['value'] = current($array);
+$eachedArray[0] = key($array);
+$eachedArray['key'] = key($array);
+next($array);
```

<br><br>

### `StringifyDefineRector`

- class: [`Rector\Php72\Rector\FuncCall\StringifyDefineRector`](/rules/php72/src/Rector/FuncCall/StringifyDefineRector.php)
- [test fixtures](/rules/php72/tests/Rector/FuncCall/StringifyDefineRector/Fixture)

Make first argument of `define()` string

```diff
 class SomeClass
 {
     public function run(int $a)
     {
-         define(CONSTANT_2, 'value');
+         define('CONSTANT_2', 'value');
          define('CONSTANT', 'value');
     }
 }
```

<br><br>

### `StringsAssertNakedRector`

- class: [`Rector\Php72\Rector\FuncCall\StringsAssertNakedRector`](/rules/php72/src/Rector/FuncCall/StringsAssertNakedRector.php)
- [test fixtures](/rules/php72/tests/Rector/FuncCall/StringsAssertNakedRector/Fixture)

String asserts must be passed directly to `assert()`

```diff
 function nakedAssert()
 {
-    assert('true === true');
-    assert("true === true");
+    assert(true === true);
+    assert(true === true);
 }
```

<br><br>

### `UnsetCastRector`

- class: [`Rector\Php72\Rector\Unset_\UnsetCastRector`](/rules/php72/src/Rector/Unset_/UnsetCastRector.php)
- [test fixtures](/rules/php72/tests/Rector/Unset_/UnsetCastRector/Fixture)

Removes (unset) cast

```diff
-$different = (unset) $value;
+$different = null;

-$value = (unset) $value;
+unset($value);
```

<br><br>

### `WhileEachToForeachRector`

- class: [`Rector\Php72\Rector\While_\WhileEachToForeachRector`](/rules/php72/src/Rector/While_/WhileEachToForeachRector.php)
- [test fixtures](/rules/php72/tests/Rector/While_/WhileEachToForeachRector/Fixture)

`each()` function is deprecated, use `foreach()` instead.

```diff
-while (list($key, $callback) = each($callbacks)) {
+foreach ($callbacks as $key => $callback) {
     // ...
 }
```
```diff
-while (list($key) = each($callbacks)) {
+foreach (array_keys($callbacks) as $key) {
     // ...
 }
```

<br><br>

## Php73

### `ArrayKeyFirstLastRector`

- class: [`Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector`](/rules/php73/src/Rector/FuncCall/ArrayKeyFirstLastRector.php)
- [test fixtures](/rules/php73/tests/Rector/FuncCall/ArrayKeyFirstLastRector/Fixture)

Make use of `array_key_first()` and `array_key_last()`

```diff
-reset($items);
-$firstKey = key($items);
+$firstKey = array_key_first($items);
```
```diff
-end($items);
-$lastKey = key($items);
+$lastKey = array_key_last($items);
```

<br><br>

### `IsCountableRector`

- class: [`Rector\Php73\Rector\BinaryOp\IsCountableRector`](/rules/php73/src/Rector/BinaryOp/IsCountableRector.php)
- [test fixtures](/rules/php73/tests/Rector/BinaryOp/IsCountableRector/Fixture)

Changes `is_array` + Countable check to `is_countable`

```diff
-is_array($foo) || $foo instanceof Countable;
+is_countable($foo);
```

<br><br>

### `JsonThrowOnErrorRector`

- class: [`Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector`](/rules/php73/src/Rector/FuncCall/JsonThrowOnErrorRector.php)
- [test fixtures](/rules/php73/tests/Rector/FuncCall/JsonThrowOnErrorRector/Fixture)

Adds JSON_THROW_ON_ERROR to `json_encode()` and `json_decode()` to throw JsonException on error

```diff
-json_encode($content);
-json_decode($json);
+json_encode($content, JSON_THROW_ON_ERROR);
+json_decode($json, null, null, JSON_THROW_ON_ERROR);
```

<br><br>

### `RegexDashEscapeRector`

- class: [`Rector\Php73\Rector\FuncCall\RegexDashEscapeRector`](/rules/php73/src/Rector/FuncCall/RegexDashEscapeRector.php)
- [test fixtures](/rules/php73/tests/Rector/FuncCall/RegexDashEscapeRector/Fixture)

Escape - in some cases

```diff
-preg_match("#[\w-()]#", 'some text');
+preg_match("#[\w\-()]#", 'some text');
```

<br><br>

### `RemoveMissingCompactVariableRector`

- class: [`Rector\Php73\Rector\FuncCall\RemoveMissingCompactVariableRector`](/rules/php73/src/Rector/FuncCall/RemoveMissingCompactVariableRector.php)
- [test fixtures](/rules/php73/tests/Rector/FuncCall/RemoveMissingCompactVariableRector/Fixture)

Remove non-existing vars from `compact()`

```diff
 class SomeClass
 {
     public function run()
     {
         $value = 'yes';

-        compact('value', 'non_existing');
+        compact('value');
     }
 }
```

<br><br>

### `SensitiveConstantNameRector`

- class: [`Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector`](/rules/php73/src/Rector/ConstFetch/SensitiveConstantNameRector.php)
- [test fixtures](/rules/php73/tests/Rector/ConstFetch/SensitiveConstantNameRector/Fixture)

Changes case insensitive constants to sensitive ones.

```diff
 define('FOO', 42, true);
 var_dump(FOO);
-var_dump(foo);
+var_dump(FOO);
```

<br><br>

### `SensitiveDefineRector`

- class: [`Rector\Php73\Rector\FuncCall\SensitiveDefineRector`](/rules/php73/src/Rector/FuncCall/SensitiveDefineRector.php)
- [test fixtures](/rules/php73/tests/Rector/FuncCall/SensitiveDefineRector/Fixture)

Changes case insensitive constants to sensitive ones.

```diff
-define('FOO', 42, true);
+define('FOO', 42);
```

<br><br>

### `SensitiveHereNowDocRector`

- class: [`Rector\Php73\Rector\String_\SensitiveHereNowDocRector`](/rules/php73/src/Rector/String_/SensitiveHereNowDocRector.php)
- [test fixtures](/rules/php73/tests/Rector/String_/SensitiveHereNowDocRector/Fixture)

Changes heredoc/nowdoc that contains closing word to safe wrapper name

```diff
-$value = <<<A
+$value = <<<A_WRAP
     A
-A
+A_WRAP
```

<br><br>

### `SetCookieRector`

- class: [`Rector\Php73\Rector\FuncCall\SetCookieRector`](/rules/php73/src/Rector/FuncCall/SetCookieRector.php)
- [test fixtures](/rules/php73/tests/Rector/FuncCall/SetcookieRector/Fixture)

Convert `setcookie` argument to PHP7.3 option array

```diff
-setcookie('name', $value, 360);
+setcookie('name', $value, ['expires' => 360]);
```
```diff
-setcookie('name', $name, 0, '', '', true, true);
+setcookie('name', $name, ['expires' => 0, 'path' => '', 'domain' => '', 'secure' => true, 'httponly' => true]);
```

<br><br>

### `StringifyStrNeedlesRector`

- class: [`Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector`](/rules/php73/src/Rector/FuncCall/StringifyStrNeedlesRector.php)
- [test fixtures](/rules/php73/tests/Rector/FuncCall/StringifyStrNeedlesRector/Fixture)

Makes needles explicit strings

```diff
 $needle = 5;
-$fivePosition = strpos('725', $needle);
+$fivePosition = strpos('725', (string) $needle);
```

<br><br>

## Php74

### `AddLiteralSeparatorToNumberRector`

- class: [`Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector`](/rules/php74/src/Rector/LNumber/AddLiteralSeparatorToNumberRector.php)
- [test fixtures](/rules/php74/tests/Rector/LNumber/AddLiteralSeparatorToNumberRector/Fixture)

Add "_" as thousands separator in numbers

```diff
 class SomeClass
 {
     public function run()
     {
-        $int = 1000;
-        $float = 1000500.001;
+        $int = 1_000;
+        $float = 1_000_500.001;
     }
 }
```

<br><br>

### `ArrayKeyExistsOnPropertyRector`

- class: [`Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector`](/rules/php74/src/Rector/FuncCall/ArrayKeyExistsOnPropertyRector.php)
- [test fixtures](/rules/php74/tests/Rector/FuncCall/ArrayKeyExistsOnPropertyRector/Fixture)

Change `array_key_exists()` on property to `property_exists()`

```diff
 class SomeClass
 {
      public $value;
 }
 $someClass = new SomeClass;

-array_key_exists('value', $someClass);
+property_exists($someClass, 'value');
```

<br><br>

### `ArraySpreadInsteadOfArrayMergeRector`

- class: [`Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector`](/rules/php74/src/Rector/FuncCall/ArraySpreadInsteadOfArrayMergeRector.php)
- [test fixtures](/rules/php74/tests/Rector/FuncCall/ArraySpreadInsteadOfArrayMergeRector/Fixture)

Change `array_merge()` to spread operator, except values with possible string `key` values

```diff
 class SomeClass
 {
     public function run($iter1, $iter2)
     {
-        $values = array_merge(iterator_to_array($iter1), iterator_to_array($iter2));
+        $values = [...$iter1, ...$iter2];

         // Or to generalize to all iterables
-        $anotherValues = array_merge(
-            is_array($iter1) ? $iter1 : iterator_to_array($iter1),
-            is_array($iter2) ? $iter2 : iterator_to_array($iter2)
-        );
+        $anotherValues = [...$iter1, ...$iter2];
     }
 }
```

<br><br>

### `ChangeReflectionTypeToStringToGetNameRector`

- class: [`Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector`](/rules/php74/src/Rector/MethodCall/ChangeReflectionTypeToStringToGetNameRector.php)
- [test fixtures](/rules/php74/tests/Rector/MethodCall/ChangeReflectionTypeToStringToGetNameRector/Fixture)

Change string calls on ReflectionType

```diff
 class SomeClass
 {
     public function go(ReflectionFunction $reflectionFunction)
     {
         $parameterReflection = $reflectionFunction->getParameters()[0];

-        $paramType = (string) $parameterReflection->getType();
+        $paramType = (string) ($parameterReflection->getType() ? $parameterReflection->getType()->getName() : null);

-        $stringValue = 'hey' . $reflectionFunction->getReturnType();
+        $stringValue = 'hey' . ($reflectionFunction->getReturnType() ? $reflectionFunction->getReturnType()->getName() : null);

         // keep
         return $reflectionFunction->getReturnType();
     }
 }
```

<br><br>

### `ClassConstantToSelfClassRector`

- class: [`Rector\Php74\Rector\Class_\ClassConstantToSelfClassRector`](/rules/php74/src/Rector/Class_/ClassConstantToSelfClassRector.php)
- [test fixtures](/rules/php74/tests/Rector/Class_/ClassConstantToSelfClassRector/Fixture)

Change `__CLASS__` to self::class

```diff
 class SomeClass
 {
    public function callOnMe()
    {
-       var_dump(__CLASS__);
+       var_dump(self::class);
    }
 }
```

<br><br>

### `ClosureToArrowFunctionRector`

- class: [`Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector`](/rules/php74/src/Rector/Closure/ClosureToArrowFunctionRector.php)
- [test fixtures](/rules/php74/tests/Rector/Closure/ClosureToArrowFunctionRector/Fixture)

Change closure to arrow function

```diff
 class SomeClass
 {
     public function run($meetups)
     {
-        return array_filter($meetups, function (Meetup $meetup) {
-            return is_object($meetup);
-        });
+        return array_filter($meetups, fn(Meetup $meetup) => is_object($meetup));
     }
 }
```

<br><br>

### `ExportToReflectionFunctionRector`

- class: [`Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector`](/rules/php74/src/Rector/StaticCall/ExportToReflectionFunctionRector.php)
- [test fixtures](/rules/php74/tests/Rector/StaticCall/ExportToReflectionFunctionRector/Fixture)

Change `export()` to ReflectionFunction alternatives

```diff
-$reflectionFunction = ReflectionFunction::export('foo');
-$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
+$reflectionFunction = new ReflectionFunction('foo');
+$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
```

<br><br>

### `FilterVarToAddSlashesRector`

- class: [`Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector`](/rules/php74/src/Rector/FuncCall/FilterVarToAddSlashesRector.php)
- [test fixtures](/rules/php74/tests/Rector/FuncCall/FilterVarToAddSlashesRector/Fixture)

Change `filter_var()` with slash escaping to `addslashes()`

```diff
 $var= "Satya's here!";
-filter_var($var, FILTER_SANITIZE_MAGIC_QUOTES);
+addslashes($var);
```

<br><br>

### `GetCalledClassToStaticClassRector`

- class: [`Rector\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector`](/rules/php74/src/Rector/FuncCall/GetCalledClassToStaticClassRector.php)
- [test fixtures](/rules/php74/tests/Rector/FuncCall/GetCalledClassToStaticClassRector/Fixture)

Change `get_called_class()` to static::class

```diff
 class SomeClass
 {
    public function callOnMe()
    {
-       var_dump(get_called_class());
+       var_dump(static::class);
    }
 }
```

<br><br>

### `MbStrrposEncodingArgumentPositionRector`

- class: [`Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector`](/rules/php74/src/Rector/FuncCall/MbStrrposEncodingArgumentPositionRector.php)
- [test fixtures](/rules/php74/tests/Rector/FuncCall/MbStrrposEncodingArgumentPositionRector/Fixture)

Change `mb_strrpos()` encoding argument position

```diff
-mb_strrpos($text, "abc", "UTF-8");
+mb_strrpos($text, "abc", 0, "UTF-8");
```

<br><br>

### `NullCoalescingOperatorRector`

- class: [`Rector\Php74\Rector\Assign\NullCoalescingOperatorRector`](/rules/php74/src/Rector/Assign/NullCoalescingOperatorRector.php)
- [test fixtures](/rules/php74/tests/Rector/Assign/NullCoalescingOperatorRector/Fixture)

Use null coalescing operator ??=

```diff
 $array = [];
-$array['user_id'] = $array['user_id'] ?? 'value';
+$array['user_id'] ??= 'value';
```

<br><br>

### `RealToFloatTypeCastRector`

- class: [`Rector\Php74\Rector\Double\RealToFloatTypeCastRector`](/rules/php74/src/Rector/Double/RealToFloatTypeCastRector.php)
- [test fixtures](/rules/php74/tests/Rector/Double/RealToFloatTypeCastRector/Fixture)

Change deprecated (real) to (float)

```diff
 class SomeClass
 {
     public function run()
     {
-        $number = (real) 5;
+        $number = (float) 5;
         $number = (float) 5;
         $number = (double) 5;
     }
 }
```

<br><br>

### `ReservedFnFunctionRector`

- class: [`Rector\Php74\Rector\Function_\ReservedFnFunctionRector`](/rules/php74/src/Rector/Function_/ReservedFnFunctionRector.php)
- [test fixtures](/rules/php74/tests/Rector/Function_/ReservedFnFunctionRector/Fixture)

Change `fn()` function name, since it will be reserved keyword

```php
<?php

declare(strict_types=1);

use Rector\Php74\Rector\Function_\ReservedFnFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReservedFnFunctionRector::class)
        ->call('configure', [[
            ReservedFnFunctionRector::RESERVED_NAMES_TO_NEW_ONES => [
                'fn' => 'someFunctionName',
            ],
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        function fn($value)
+        function f($value)
         {
             return $value;
         }

-        fn(5);
+        f(5);
     }
 }
```

<br><br>

### `RestoreDefaultNullToNullableTypePropertyRector`

- class: [`Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector`](/rules/php74/src/Rector/Property/RestoreDefaultNullToNullableTypePropertyRector.php)
- [test fixtures](/rules/php74/tests/Rector/Property/RestoreDefaultNullToNullableTypePropertyRector/Fixture)

Add null default to properties with PHP 7.4 property nullable type

```diff
 class SomeClass
 {
-    public ?string $name;
+    public ?string $name = null;
 }
```

<br><br>

### `TypedPropertyRector`

- class: [`Rector\Php74\Rector\Property\TypedPropertyRector`](/rules/php74/src/Rector/Property/TypedPropertyRector.php)
- [test fixtures](/rules/php74/tests/Rector/Property/TypedPropertyRector/Fixture)

Changes property `@var` annotations from annotation to type.

```php
<?php

declare(strict_types=1);

use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(TypedPropertyRector::class)
        ->call('configure', [[
            TypedPropertyRector::CLASS_LIKE_TYPE_ONLY => false
        ]]);
};
```

↓

```diff
 final class SomeClass
 {
-    /**
-     * @var int
-     */
-    private count;
+    private int count;
 }
```

<br><br>

## Php80

### `AnnotationToAttributeRector`

- class: [`Rector\Php80\Rector\Class_\AnnotationToAttributeRector`](/rules/php80/src/Rector/Class_/AnnotationToAttributeRector.php)
- [test fixtures](/rules/php80/tests/Rector/Class_/AnnotationToAttributeRector/Fixture)

Change annotation to attribute

```diff
 use Doctrine\ORM\Attributes as ORM;

-/**
-  * @ORM\Entity
-  */
+#[ORM\Entity]
 class SomeClass
 {
 }
```

<br><br>

### `ChangeSwitchToMatchRector`

- class: [`Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector`](/rules/php80/src/Rector/Switch_/ChangeSwitchToMatchRector.php)
- [test fixtures](/rules/php80/tests/Rector/Switch_/ChangeSwitchToMatchRector/Fixture)

Change `switch()` to `match()`

```diff
 class SomeClass
 {
     public function run()
     {
-        $statement = switch ($this->lexer->lookahead['type']) {
-            case Lexer::T_SELECT:
-                $statement = $this->SelectStatement();
-                break;
-
-            case Lexer::T_UPDATE:
-                $statement = $this->UpdateStatement();
-                break;
-
-            case Lexer::T_DELETE:
-                $statement = $this->DeleteStatement();
-                break;
-
-            default:
-                $this->syntaxError('SELECT, UPDATE or DELETE');
-                break;
-        }
+        $statement = match ($this->lexer->lookahead['type']) {
+            Lexer::T_SELECT => $this->SelectStatement(),
+            Lexer::T_UPDATE => $this->UpdateStatement(),
+            Lexer::T_DELETE => $this->DeleteStatement(),
+            default => $this->syntaxError('SELECT, UPDATE or DELETE'),
+        };
     }
 }
```

<br><br>

### `ClassOnObjectRector`

- class: [`Rector\Php80\Rector\FuncCall\ClassOnObjectRector`](/rules/php80/src/Rector/FuncCall/ClassOnObjectRector.php)
- [test fixtures](/rules/php80/tests/Rector/FuncCall/ClassOnObjectRector/Fixture)

Change get_class($object) to faster `$object::class`

```diff
 class SomeClass
 {
     public function run($object)
     {
-        return get_class($object);
+        return $object::class;
     }
 }
```

<br><br>

### `ClassPropertyAssignToConstructorPromotionRector`

- class: [`Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector`](/rules/php80/src/Rector/Class_/ClassPropertyAssignToConstructorPromotionRector.php)
- [test fixtures](/rules/php80/tests/Rector/Class_/ClassPropertyAssignToConstructorPromotionRector/Fixture)

Change simple property init and assign to constructor promotion

```diff
 class SomeClass
 {
-    public float $x;
-    public float $y;
-    public float $z;
-
     public function __construct(
-        float $x = 0.0,
-        float $y = 0.0,
-        float $z = 0.0
-    ) {
-        $this->x = $x;
-        $this->y = $y;
-        $this->z = $z;
-    }
+        public float $x = 0.0,
+        public float $y = 0.0,
+        public float $z = 0.0,
+    ) {}
 }
```

<br><br>

### `GetDebugTypeRector`

- class: [`Rector\Php80\Rector\Ternary\GetDebugTypeRector`](/rules/php80/src/Rector/Ternary/GetDebugTypeRector.php)
- [test fixtures](/rules/php80/tests/Rector/Ternary/GetDebugTypeRector/Fixture)

Change ternary type resolve to `get_debug_type()`

```diff
 class SomeClass
 {
     public function run($value)
     {
-        return is_object($value) ? get_class($value) : gettype($value);
+        return get_debug_type($value);
     }
 }
```

<br><br>

### `RemoveUnusedVariableInCatchRector`

- class: [`Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector`](/rules/php80/src/Rector/Catch_/RemoveUnusedVariableInCatchRector.php)
- [test fixtures](/rules/php80/tests/Rector/Catch_/RemoveUnusedVariableInCatchRector/Fixture)

Remove unused variable in `catch()`

```diff
 final class SomeClass
 {
     public function run()
     {
         try {
-        } catch (Throwable $notUsedThrowable) {
+        } catch (Throwable) {
         }
     }
 }
```

<br><br>

### `StrContainsRector`

- class: [`Rector\Php80\Rector\NotIdentical\StrContainsRector`](/rules/php80/src/Rector/NotIdentical/StrContainsRector.php)
- [test fixtures](/rules/php80/tests/Rector/NotIdentical/StrContainsRector/Fixture)

Replace `strpos()` !== false and `strstr()`  with `str_contains()`

```diff
 class SomeClass
 {
     public function run()
     {
-        return strpos('abc', 'a') !== false;
+        return str_contains('abc', 'a');
     }
 }
```

<br><br>

### `StrEndsWithRector`

- class: [`Rector\Php80\Rector\Identical\StrEndsWithRector`](/rules/php80/src/Rector/Identical/StrEndsWithRector.php)
- [test fixtures](/rules/php80/tests/Rector/Identical/StrEndsWithRector/Fixture)

Change helper functions to `str_ends_with()`

```diff
 class SomeClass
 {
     public function run()
     {
-        $isMatch = substr($haystack, -strlen($needle)) === $needle;
+        $isMatch = str_ends_with($haystack, $needle);
     }
 }
```

<br><br>

### `StrStartsWithRector`

- class: [`Rector\Php80\Rector\Identical\StrStartsWithRector`](/rules/php80/src/Rector/Identical/StrStartsWithRector.php)
- [test fixtures](/rules/php80/tests/Rector/Identical/StrStartsWithRector/Fixture)

Change helper functions to `str_starts_with()`

```diff
 class SomeClass
 {
     public function run()
     {
-        $isMatch = substr($haystack, 0, strlen($needle)) === $needle;
+        $isMatch = str_starts_with($haystack, $needle);

-        $isNotMatch = substr($haystack, 0, strlen($needle)) !== $needle;
+        $isNotMatch = ! str_starts_with($haystack, $needle);
     }
 }
```

<br><br>

### `StringableForToStringRector`

- class: [`Rector\Php80\Rector\Class_\StringableForToStringRector`](/rules/php80/src/Rector/Class_/StringableForToStringRector.php)
- [test fixtures](/rules/php80/tests/Rector/Class_/StringableForToStringRector/Fixture)

Add `Stringable` interface to classes with `__toString()` method

```diff
-class SomeClass
+class SomeClass implements Stringable
 {
-    public function __toString()
+    public function __toString(): string
     {
         return 'I can stringz';
     }
 }
```

<br><br>

### `TokenGetAllToObjectRector`

- class: [`Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector`](/rules/php80/src/Rector/FuncCall/TokenGetAllToObjectRector.php)
- [test fixtures](/rules/php80/tests/Rector/FuncCall/TokenGetAllToObjectRector/Fixture)

Complete missing constructor dependency instance by type

```diff
 final class SomeClass
 {
     public function run()
     {
-        $tokens = token_get_all($code);
-        foreach ($tokens as $token) {
-            if (is_array($token)) {
-               $name = token_name($token[0]);
-               $text = $token[1];
-            } else {
-               $name = null;
-               $text = $token;
-            }
+        $tokens = \PhpToken::getAll($code);
+        foreach ($tokens as $phpToken) {
+           $name = $phpToken->getTokenName();
+           $text = $phpToken->text;
         }
     }
 }
```

<br><br>

### `UnionTypesRector`

- class: [`Rector\Php80\Rector\FunctionLike\UnionTypesRector`](/rules/php80/src/Rector/FunctionLike/UnionTypesRector.php)
- [test fixtures](/rules/php80/tests/Rector/FunctionLike/UnionTypesRector/Fixture)

Change docs types to union types, where possible (properties are covered by TypedPropertiesRector)

```diff
 class SomeClass
 {
     /**
      * @param array|int $number
      * @return bool|float
      */
-    public function go($number)
+    public function go(array|int $number): bool|float
     {
     }
 }
```

<br><br>

## PhpDeglobalize

### `ChangeGlobalVariablesToPropertiesRector`

- class: [`Rector\PhpDeglobalize\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector`](/rules/php-deglobalize/src/Rector/ClassMethod/ChangeGlobalVariablesToPropertiesRector.php)
- [test fixtures](/rules/php-deglobalize/tests/Rector/ClassMethod/ChangeGlobalVariablesToPropertiesRector/Fixture)

Change global `$variables` to private properties

```diff
 class SomeClass
 {
+    private $variable;
     public function go()
     {
-        global $variable;
-        $variable = 5;
+        $this->variable = 5;
     }

     public function run()
     {
-        global $variable;
-        var_dump($variable);
+        var_dump($this->variable);
     }
 }
```

<br><br>

## PhpSpecToPHPUnit

### `AddMockPropertiesRector`

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector`](/rules/php-spec-to-phpunit/src/Rector/Class_/AddMockPropertiesRector.php)

Migrate PhpSpec behavior to PHPUnit test

```diff
-
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br><br>

### `MockVariableToPropertyFetchRector`

- class: [`Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector`](/rules/php-spec-to-phpunit/src/Rector/Variable/MockVariableToPropertyFetchRector.php)

Migrate PhpSpec behavior to PHPUnit test

```diff
-
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br><br>

### `PhpSpecClassToPHPUnitClassRector`

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector`](/rules/php-spec-to-phpunit/src/Rector/Class_/PhpSpecClassToPHPUnitClassRector.php)

Migrate PhpSpec behavior to PHPUnit test

```diff
-
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br><br>

### `PhpSpecMethodToPHPUnitMethodRector`

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector`](/rules/php-spec-to-phpunit/src/Rector/ClassMethod/PhpSpecMethodToPHPUnitMethodRector.php)

Migrate PhpSpec behavior to PHPUnit test

```diff
-
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br><br>

### `PhpSpecMocksToPHPUnitMocksRector`

- class: [`Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector`](/rules/php-spec-to-phpunit/src/Rector/MethodCall/PhpSpecMocksToPHPUnitMocksRector.php)

Migrate PhpSpec behavior to PHPUnit test

```diff
-
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br><br>

### `PhpSpecPromisesToPHPUnitAssertRector`

- class: [`Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector`](/rules/php-spec-to-phpunit/src/Rector/MethodCall/PhpSpecPromisesToPHPUnitAssertRector.php)

Migrate PhpSpec behavior to PHPUnit test

```diff
-
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br><br>

### `RenameSpecFileToTestFileRector`

- class: [`Rector\PhpSpecToPHPUnit\Rector\FileSystem\RenameSpecFileToTestFileRector`](/rules/php-spec-to-phpunit/src/Rector/FileSystem/RenameSpecFileToTestFileRector.php)

Rename "*Spec.php" file to "*Test.php" file

```diff
-// tests/SomeSpec.php
+// tests/SomeTest.php
```

<br><br>

## Polyfill

### `UnwrapFutureCompatibleIfFunctionExistsRector`

- class: [`Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector`](/rules/polyfill/src/Rector/If_/UnwrapFutureCompatibleIfFunctionExistsRector.php)
- [test fixtures](/rules/polyfill/tests/Rector/If_/UnwrapFutureCompatibleIfFunctionExistsRector/Fixture)

Remove functions exists if with else for always existing

```diff
 class SomeClass
 {
     public function run()
     {
         // session locking trough other addons
-        if (function_exists('session_abort')) {
-            session_abort();
-        } else {
-            session_write_close();
-        }
+        session_abort();
     }
 }
```

<br><br>

### `UnwrapFutureCompatibleIfPhpVersionRector`

- class: [`Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector`](/rules/polyfill/src/Rector/If_/UnwrapFutureCompatibleIfPhpVersionRector.php)
- [test fixtures](/rules/polyfill/tests/Rector/If_/UnwrapFutureCompatibleIfPhpVersionRector/Fixture)

Remove php version checks if they are passed

```diff
 // current PHP: 7.2
-if (version_compare(PHP_VERSION, '7.2', '<')) {
-    return 'is PHP 7.1-';
-} else {
-    return 'is PHP 7.2+';
-}
+return 'is PHP 7.2+';
```

<br><br>

## Privatization

### `ChangeLocalPropertyToVariableRector`

- class: [`Rector\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector`](/rules/privatization/src/Rector/Class_/ChangeLocalPropertyToVariableRector.php)
- [test fixtures](/rules/privatization/tests/Rector/Class_/ChangeLocalPropertyToVariableRector/Fixture)

Change local property used in single method to local variable

```diff
 class SomeClass
 {
-    private $count;
     public function run()
     {
-        $this->count = 5;
-        return $this->count;
+        $count = 5;
+        return $count;
     }
 }
```

<br><br>

### `PrivatizeFinalClassMethodRector`

- class: [`Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector`](/rules/privatization/src/Rector/ClassMethod/PrivatizeFinalClassMethodRector.php)
- [test fixtures](/rules/privatization/tests/Rector/ClassMethod/PrivatizeFinalClassMethodRector/Fixture)

Change protected class method to private if possible

```diff
 final class SomeClass
 {
-    protected function someMethod()
+    private function someMethod()
     {
     }
 }
```

<br><br>

### `PrivatizeFinalClassPropertyRector`

- class: [`Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector`](/rules/privatization/src/Rector/Property/PrivatizeFinalClassPropertyRector.php)
- [test fixtures](/rules/privatization/tests/Rector/Property/PrivatizeFinalClassPropertyRector/Fixture)

Change property to private if possible

```diff
 final class SomeClass
 {
-    protected $value;
+    private $value;
 }
```

<br><br>

### `PrivatizeLocalClassConstantRector`

- class: [`Rector\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector`](/rules/privatization/src/Rector/ClassConst/PrivatizeLocalClassConstantRector.php)
- [test fixtures](/rules/privatization/tests/Rector/ClassConst/PrivatizeLocalClassConstantRector/Fixture)

Finalize every class constant that is used only locally

```diff
 class ClassWithConstantUsedOnlyHere
 {
-    const LOCAL_ONLY = true;
+    private const LOCAL_ONLY = true;

     public function isLocalOnly()
     {
         return self::LOCAL_ONLY;
     }
 }
```

<br><br>

### `PrivatizeLocalGetterToPropertyRector`

- class: [`Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector`](/rules/privatization/src/Rector/MethodCall/PrivatizeLocalGetterToPropertyRector.php)
- [test fixtures](/rules/privatization/tests/Rector/MethodCall/PrivatizeLocalGetterToPropertyRector/Fixture)

Privatize getter of local property to property

```diff
 class SomeClass
 {
     private $some;

     public function run()
     {
-        return $this->getSome() + 5;
+        return $this->some + 5;
     }

     private function getSome()
     {
         return $this->some;
     }
 }
```

<br><br>

### `PrivatizeLocalOnlyMethodRector`

- class: [`Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector`](/rules/privatization/src/Rector/ClassMethod/PrivatizeLocalOnlyMethodRector.php)
- [test fixtures](/rules/privatization/tests/Rector/ClassMethod/PrivatizeLocalOnlyMethodRector/Fixture)

Privatize local-only use methods

```diff
 class SomeClass
 {
     /**
      * @api
      */
     public function run()
     {
         return $this->useMe();
     }

-    public function useMe()
+    private function useMe()
     {
     }
 }
```

<br><br>

### `PrivatizeLocalPropertyToPrivatePropertyRector`

- class: [`Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector`](/rules/privatization/src/Rector/Property/PrivatizeLocalPropertyToPrivatePropertyRector.php)
- [test fixtures](/rules/privatization/tests/Rector/Property/PrivatizeLocalPropertyToPrivatePropertyRector/Fixture)

Privatize local-only property to private property

```diff
 class SomeClass
 {
-    public $value;
+    private $value;

     public function run()
     {
         return $this->value;
     }
 }
```

<br><br>

## RectorGenerator

### `AddNewServiceToSymfonyPhpConfigRector`

- class: [`Rector\RectorGenerator\Rector\Closure\AddNewServiceToSymfonyPhpConfigRector`](/packages/rector-generator/src/Rector/Closure/AddNewServiceToSymfonyPhpConfigRector.php)

Adds a new `$services->set(...)` call to PHP Config

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();
+    $services->set(AddNewServiceToSymfonyPhpConfigRector::class);
 };
```

<br><br>

## RemovingStatic

### `LocallyCalledStaticMethodToNonStaticRector`

- class: [`Rector\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector`](/rules/removing-static/src/Rector/ClassMethod/LocallyCalledStaticMethodToNonStaticRector.php)
- [test fixtures](/rules/removing-static/tests/Rector/ClassMethod/LocallyCalledStaticMethodToNonStaticRector/Fixture)

Change static method and local-only calls to non-static

```diff
 class SomeClass
 {
     public function run()
     {
-        self::someStatic();
+        $this->someStatic();
     }

-    private static function someStatic()
+    private function someStatic()
     {
     }
 }
```

<br><br>

### `NewUniqueObjectToEntityFactoryRector`

- class: [`Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector`](/rules/removing-static/src/Rector/Class_/NewUniqueObjectToEntityFactoryRector.php)

Convert new X to new factories

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewUniqueObjectToEntityFactoryRector::class)
        ->call('configure', [[
            NewUniqueObjectToEntityFactoryRector::TYPES_TO_SERVICES => ['ClassName'],
        ]]);
};
```

↓

```diff
-<?php
-
 class SomeClass
 {
+    public function __construct(AnotherClassFactory $anotherClassFactory)
+    {
+        $this->anotherClassFactory = $anotherClassFactory;
+    }
+
     public function run()
     {
-        return new AnotherClass;
+        return $this->anotherClassFactory->create();
     }
 }

 class AnotherClass
 {
     public function someFun()
     {
         return StaticClass::staticMethod();
     }
 }
```

<br><br>

### `PHPUnitStaticToKernelTestCaseGetRector`

- class: [`Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector`](/rules/removing-static/src/Rector/Class_/PHPUnitStaticToKernelTestCaseGetRector.php)
- [test fixtures](/rules/removing-static/tests/Rector/Class_/PHPUnitStaticToKernelTestCaseGetRector/Fixture)

Convert static calls in PHPUnit test cases, to `get()` from the container of KernelTestCase

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PHPUnitStaticToKernelTestCaseGetRector::class)
        ->call('configure', [[
            PHPUnitStaticToKernelTestCaseGetRector::STATIC_CLASS_TYPES => ['EntityFactory'],
        ]]);
};
```

↓

```diff
-<?php
+use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

-use PHPUnit\Framework\TestCase;
+final class SomeTestCase extends KernelTestCase
+{
+    /**
+     * @var EntityFactory
+     */
+    private $entityFactory;

-final class SomeTestCase extends TestCase
-{
+    protected function setUp(): void
+    {
+        parent::setUp();
+        $this->entityFactory = self::$container->get(EntityFactory::class);
+    }
+
     public function test()
     {
-        $product = EntityFactory::create('product');
+        $product = $this->entityFactory->create('product');
     }
 }
```

<br><br>

### `PassFactoryToUniqueObjectRector`

- class: [`Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector`](/rules/removing-static/src/Rector/Class_/PassFactoryToUniqueObjectRector.php)

Convert new `X/Static::call()` to factories in entities, pass them via constructor to `each` other

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PassFactoryToUniqueObjectRector::class)
        ->call('configure', [[
            PassFactoryToUniqueObjectRector::TYPES_TO_SERVICES => ['StaticClass'],
        ]]);
};
```

↓

```diff
-<?php
-
 class SomeClass
 {
+    public function __construct(AnotherClassFactory $anotherClassFactory)
+    {
+        $this->anotherClassFactory = $anotherClassFactory;
+    }
+
     public function run()
     {
-        return new AnotherClass;
+        return $this->anotherClassFactory->create();
     }
 }

 class AnotherClass
 {
+    public function __construct(StaticClass $staticClass)
+    {
+        $this->staticClass = $staticClass;
+    }
+
     public function someFun()
     {
-        return StaticClass::staticMethod();
+        return $this->staticClass->staticMethod();
+    }
+}
+
+final class AnotherClassFactory
+{
+    /**
+     * @var StaticClass
+     */
+    private $staticClass;
+
+    public function __construct(StaticClass $staticClass)
+    {
+        $this->staticClass = $staticClass;
+    }
+
+    public function create(): AnotherClass
+    {
+        return new AnotherClass($this->staticClass);
     }
 }
```

<br><br>

### `SingleStaticServiceToDynamicRector`

- class: [`Rector\RemovingStatic\Rector\Class_\SingleStaticServiceToDynamicRector`](/rules/removing-static/src/Rector/Class_/SingleStaticServiceToDynamicRector.php)
- [test fixtures](/rules/removing-static/tests/Rector/Class_/SingleStaticServiceToDynamicRector/Fixture)

Change full static service, to dynamic one

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\SingleStaticServiceToDynamicRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SingleStaticServiceToDynamicRector::class)
        ->call('configure', [[
            SingleStaticServiceToDynamicRector::CLASS_TYPES => ['SomeClass'],
        ]]);
};
```

↓

```diff
 class AnotherClass
 {
+    /**
+     * @var SomeClass
+     */
+    private $someClass;
+
+    public fuction __construct(SomeClass $someClass)
+    {
+        $this->someClass = $someClass;
+    }
+
     public function run()
     {
         SomeClass::someStatic();
     }
 }

 class SomeClass
 {
-    public static function run()
+    public function run()
     {
-        self::someStatic();
+        $this->someStatic();
     }

-    private static function someStatic()
+    private function someStatic()
     {
     }
 }
```

<br><br>

### `StaticTypeToSetterInjectionRector`

- class: [`Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector`](/rules/removing-static/src/Rector/Class_/StaticTypeToSetterInjectionRector.php)
- [test fixtures](/rules/removing-static/tests/Rector/Class_/StaticTypeToSetterInjectionRector/Fixture)

Changes types to setter injection

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticTypeToSetterInjectionRector::class)
        ->call('configure', [[
            StaticTypeToSetterInjectionRector::STATIC_TYPES => ['SomeStaticClass'],
        ]]);
};
```

↓

```diff
 <?php

 final class CheckoutEntityFactory
 {
+    /**
+     * @var SomeStaticClass
+     */
+    private $someStaticClass;
+
+    public function setSomeStaticClass(SomeStaticClass $someStaticClass)
+    {
+        $this->someStaticClass = $someStaticClass;
+    }
+
     public function run()
     {
-        return SomeStaticClass::go();
+        return $this->someStaticClass->go();
     }
 }
```

<br><br>

## Renaming

### `RenameAnnotationRector`

- class: [`Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector`](/rules/renaming/src/Rector/ClassMethod/RenameAnnotationRector.php)
- [test fixtures](/rules/renaming/tests/Rector/ClassMethod/RenameAnnotationRector/Fixture)

Turns defined annotations above properties and methods to their new values.

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotation;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameAnnotationRector::class)
        ->call(
            'configure',
            [[
                RenameAnnotationRector::RENAMED_ANNOTATIONS_IN_TYPES => inline_value_objects(
                                [new RenameAnnotation('PHPUnit\Framework\TestCase', 'test', 'scenario')]
                            )
            ]]
        );
};
```

↓

```diff
 class SomeTest extends PHPUnit\Framework\TestCase
 {
     /**
-     * @test
+     * @scenario
      */
     public function someMethod()
     {
     }
 }
```

<br><br>

### `RenameClassConstantRector`

- class: [`Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector`](/rules/renaming/src/Rector/ClassConstFetch/RenameClassConstantRector.php)
- [test fixtures](/rules/renaming/tests/Rector/ClassConstFetch/RenameClassConstantRector/Fixture)

Replaces defined class constants in their calls.

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\ValueObject\RenameClassConstant;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassConstantRector::class)
        ->call(
            'configure',
            [[
                RenameClassConstantRector::CLASS_CONSTANT_RENAME => inline_value_objects(
                                [new RenameClassConstant('SomeClass', 'OLD_CONSTANT', 'NEW_CONSTANT'), new RenameClassConstant(
                                    'SomeClass',
                                    'OTHER_OLD_CONSTANT',
                                    'DifferentClass::NEW_CONSTANT'
                                )]
                            )
            ]]
        );
};
```

↓

```diff
-$value = SomeClass::OLD_CONSTANT;
-$value = SomeClass::OTHER_OLD_CONSTANT;
+$value = SomeClass::NEW_CONSTANT;
+$value = DifferentClass::NEW_CONSTANT;
```

<br><br>

### `RenameClassRector`

- class: [`Rector\Renaming\Rector\Name\RenameClassRector`](/rules/renaming/src/Rector/Name/RenameClassRector.php)
- [test fixtures](/rules/renaming/tests/Rector/Name/RenameClassRector/Fixture)

Replaces defined classes by new ones.

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'App\SomeOldClass' => 'App\SomeNewClass',
            ],
        ]]);
};
```

↓

```diff
 namespace App;

-use SomeOldClass;
+use SomeNewClass;

-function someFunction(SomeOldClass $someOldClass): SomeOldClass
+function someFunction(SomeNewClass $someOldClass): SomeNewClass
 {
-    if ($someOldClass instanceof SomeOldClass) {
-        return new SomeOldClass;
+    if ($someOldClass instanceof SomeNewClass) {
+        return new SomeNewClass;
     }
 }
```

<br><br>

### `RenameConstantRector`

- class: [`Rector\Renaming\Rector\ConstFetch\RenameConstantRector`](/rules/renaming/src/Rector/ConstFetch/RenameConstantRector.php)
- [test fixtures](/rules/renaming/tests/Rector/ConstFetch/RenameConstantRector/Fixture)

Replace constant by new ones

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameConstantRector::class)
        ->call(
            'configure',
            [[
                RenameConstantRector::OLD_TO_NEW_CONSTANTS => [
                    'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
                    'OLD_CONSTANT' => 'NEW_CONSTANT',
                ],
            ]]
        );
};
```

↓

```diff
 final class SomeClass
 {
     public function run()
     {
-        return MYSQL_ASSOC;
+        return MYSQLI_ASSOC;
     }
 }
```

<br><br>

### `RenameFunctionRector`

- class: [`Rector\Renaming\Rector\FuncCall\RenameFunctionRector`](/rules/renaming/src/Rector/FuncCall/RenameFunctionRector.php)
- [test fixtures](/rules/renaming/tests/Rector/FuncCall/RenameFunctionRector/Fixture)

Turns defined function call new one.

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameFunctionRector::class)
        ->call(
            'configure',
            [[
                RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
                    'view' => 'Laravel\Templating\render',
                ],
            ]]
        );
};
```

↓

```diff
-view("...", []);
+Laravel\Templating\render("...", []);
```

<br><br>

### `RenameMethodRector`

- class: [`Rector\Renaming\Rector\MethodCall\RenameMethodRector`](/rules/renaming/src/Rector/MethodCall/RenameMethodRector.php)
- [test fixtures](/rules/renaming/tests/Rector/MethodCall/RenameMethodRector/Fixture)

Turns method names to new ones.

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call(
            'configure',
            [[
                RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects(
                                [new MethodCallRename('SomeExampleClass', 'oldMethod', 'newMethod')]
                            )
            ]]
        );
};
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->oldMethod();
+$someObject->newMethod();
```

<br><br>

### `RenameNamespaceRector`

- class: [`Rector\Renaming\Rector\Namespace_\RenameNamespaceRector`](/rules/renaming/src/Rector/Namespace_/RenameNamespaceRector.php)
- [test fixtures](/rules/renaming/tests/Rector/Namespace_/RenameNamespaceRector/Fixture)

Replaces old namespace by new one.

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Namespace_\RenameNamespaceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameNamespaceRector::class)
        ->call(
            'configure',
            [[
                RenameNamespaceRector::OLD_TO_NEW_NAMESPACES => [
                    'SomeOldNamespace' => 'SomeNewNamespace',
                ],
            ]]
        );
};
```

↓

```diff
-$someObject = new SomeOldNamespace\SomeClass;
+$someObject = new SomeNewNamespace\SomeClass;
```

<br><br>

### `RenamePropertyRector`

- class: [`Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector`](/rules/renaming/src/Rector/PropertyFetch/RenamePropertyRector.php)
- [test fixtures](/rules/renaming/tests/Rector/PropertyFetch/RenamePropertyRector/Fixture)

Replaces defined old properties by new ones.

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\RenameProperty;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenamePropertyRector::class)
        ->call(
            'configure',
            [[
                RenamePropertyRector::RENAMED_PROPERTIES => inline_value_objects(
                                [new RenameProperty('SomeClass', 'someOldProperty', 'someNewProperty')]
                            )
            ]]
        );
};
```

↓

```diff
-$someObject->someOldProperty;
+$someObject->someNewProperty;
```

<br><br>

### `RenameStaticMethodRector`

- class: [`Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector`](/rules/renaming/src/Rector/StaticCall/RenameStaticMethodRector.php)
- [test fixtures](/rules/renaming/tests/Rector/StaticCall/RenameStaticMethodRector/Fixture)

Turns method names to new ones.

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameStaticMethodRector::class)
        ->call(
            'configure',
            [[
                RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => inline_value_objects(
                                [new RenameStaticMethod('SomeClass', 'oldMethod', 'AnotherExampleClass', 'newStaticMethod')]
                            )
            ]]
        );
};
```

↓

```diff
-SomeClass::oldStaticMethod();
+AnotherExampleClass::newStaticMethod();
```
```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameStaticMethodRector::class)
        ->call(
            'configure',
            [[
                RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => inline_value_objects(
                                [new RenameStaticMethod('SomeClass', 'oldMethod', 'SomeClass', 'newStaticMethod')]
                            )
            ]]
        );
};
```

↓

```diff
-SomeClass::oldStaticMethod();
+SomeClass::newStaticMethod();
```

<br><br>

## Restoration

### `CompleteImportForPartialAnnotationRector`

- class: [`Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector`](/rules/restoration/src/Rector/Namespace_/CompleteImportForPartialAnnotationRector.php)
- [test fixtures](/rules/restoration/tests/Rector/Namespace_/CompleteImportForPartialAnnotationRector/Fixture)

In case you have accidentally removed use imports but code still contains partial use statements, this will save you

```php
<?php

declare(strict_types=1);

use Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector;
use Rector\Restoration\ValueObject\UseWithAlias;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CompleteImportForPartialAnnotationRector::class)
        ->call(
            'configure',
            [[
                CompleteImportForPartialAnnotationRector::USE_IMPORTS_TO_RESTORE => inline_value_objects(
                                [new UseWithAlias('Doctrine\ORM\Mapping', 'ORM')]
                            )
            ]]
        );
};
```

↓

```diff
+use Doctrine\ORM\Mapping as ORM;
+
 class SomeClass
 {
     /**
      * @ORM\Id
      */
     public $id;
 }
```

<br><br>

### `CompleteMissingDependencyInNewRector`

- class: [`Rector\Restoration\Rector\New_\CompleteMissingDependencyInNewRector`](/rules/restoration/src/Rector/New_/CompleteMissingDependencyInNewRector.php)
- [test fixtures](/rules/restoration/tests/Rector/New_/CompleteMissingDependencyInNewRector/Fixture)

Complete missing constructor dependency instance by type

```php
<?php

declare(strict_types=1);

use Rector\Restoration\Rector\New_\CompleteMissingDependencyInNewRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CompleteMissingDependencyInNewRector::class)
        ->call(
            'configure',
            [[
                CompleteMissingDependencyInNewRector::CLASS_TO_INSTANTIATE_BY_TYPE => [
                    'RandomDependency' => 'RandomDependency',
                ],
            ]]
        );
};
```

↓

```diff
 final class SomeClass
 {
     public function run()
     {
-        $valueObject = new RandomValueObject();
+        $valueObject = new RandomValueObject(new RandomDependency());
     }
 }

 class RandomValueObject
 {
     public function __construct(RandomDependency $randomDependency)
     {
     }
 }
```

<br><br>

### `MakeTypedPropertyNullableIfCheckedRector`

- class: [`Rector\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector`](/rules/restoration/src/Rector/Property/MakeTypedPropertyNullableIfCheckedRector.php)
- [test fixtures](/rules/restoration/tests/Rector/Property/MakeTypedPropertyNullableIfCheckedRector/Fixture)

Make typed property nullable if checked

```diff
 final class SomeClass
 {
-    private AnotherClass $anotherClass;
+    private ?AnotherClass $anotherClass = null;

     public function run()
     {
         if ($this->anotherClass === null) {
             $this->anotherClass = new AnotherClass;
         }
     }
 }
```

<br><br>

### `MissingClassConstantReferenceToStringRector`

- class: [`Rector\Restoration\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector`](/rules/restoration/src/Rector/ClassConstFetch/MissingClassConstantReferenceToStringRector.php)
- [test fixtures](/rules/restoration/tests/Rector/ClassConstFetch/MissingClassConstantReferenceToStringRector/Fixture)

Convert missing class reference to string

```diff
 class SomeClass
 {
     public function run()
     {
-        return NonExistingClass::class;
+        return 'NonExistingClass';
     }
 }
```

<br><br>

### `RemoveFinalFromEntityRector`

- class: [`Rector\Restoration\Rector\Class_\RemoveFinalFromEntityRector`](/rules/restoration/src/Rector/Class_/RemoveFinalFromEntityRector.php)
- [test fixtures](/rules/restoration/tests/Rector/Class_/RemoveFinalFromEntityRector/Fixture)

Remove final from Doctrine entities

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
-final class SomeClass
+class SomeClass
 {
 }
```

<br><br>

### `RemoveUselessJustForSakeInterfaceRector`

- class: [`Rector\Restoration\Rector\Class_\RemoveUselessJustForSakeInterfaceRector`](/rules/restoration/src/Rector/Class_/RemoveUselessJustForSakeInterfaceRector.php)
- [test fixtures](/rules/restoration/tests/Rector/Class_/RemoveUselessJustForSakeInterfaceRector/Fixture)

Remove interface, that are added just for its sake, but nowhere useful

```diff
-class SomeClass implements OnlyHereUsedInterface
+class SomeClass
 {
 }

-interface OnlyHereUsedInterface
-{
-}
-
 class SomePresenter
 {
-    public function __construct(OnlyHereUsedInterface $onlyHereUsed)
+    public function __construct(SomeClass $onlyHereUsed)
     {
     }
 }
```

<br><br>

### `UpdateFileNameByClassNameFileSystemRector`

- class: [`Rector\Restoration\Rector\FileSystem\UpdateFileNameByClassNameFileSystemRector`](/rules/restoration/src/Rector/FileSystem/UpdateFileNameByClassNameFileSystemRector.php)
- [test fixtures](/rules/restoration/tests/Rector/FileSystem/UpdateFileNameByClassNameFileSystemRector/Fixture)

Rename file to respect class name

```diff
-// app/SomeClass.php
+// app/AnotherClass.php
 class AnotherClass
 {
 }
```

<br><br>

## SOLID

### `AddFalseDefaultToBoolPropertyRector`

- class: [`Rector\SOLID\Rector\Property\AddFalseDefaultToBoolPropertyRector`](/rules/solid/src/Rector/Property/AddFalseDefaultToBoolPropertyRector.php)
- [test fixtures](/rules/solid/tests/Rector/Property/AddFalseDefaultToBoolPropertyRector/Fixture)

Add false default to bool properties, to prevent null compare errors

```diff
 class SomeClass
 {
     /**
      * @var bool
      */
-    private $isDisabled;
+    private $isDisabled = false;
 }
```

<br><br>

### `ChangeIfElseValueAssignToEarlyReturnRector`

- class: [`Rector\SOLID\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector`](/rules/solid/src/Rector/If_/ChangeIfElseValueAssignToEarlyReturnRector.php)
- [test fixtures](/rules/solid/tests/Rector/If_/ChangeIfElseValueAssignToEarlyReturnRector/Fixture)

Change if/else value to early return

```diff
 class SomeClass
 {
     public function run()
     {
         if ($this->hasDocBlock($tokens, $index)) {
-            $docToken = $tokens[$this->getDocBlockIndex($tokens, $index)];
-        } else {
-            $docToken = null;
+            return $tokens[$this->getDocBlockIndex($tokens, $index)];
         }
-
-        return $docToken;
+        return null;
     }
 }
```

<br><br>

### `ChangeNestedForeachIfsToEarlyContinueRector`

- class: [`Rector\SOLID\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector`](/rules/solid/src/Rector/Foreach_/ChangeNestedForeachIfsToEarlyContinueRector.php)
- [test fixtures](/rules/solid/tests/Rector/Foreach_/ChangeNestedForeachIfsToEarlyContinueRector/Fixture)

Change nested ifs to foreach with continue

```diff
 class SomeClass
 {
     public function run()
     {
         $items = [];

         foreach ($values as $value) {
-            if ($value === 5) {
-                if ($value2 === 10) {
-                    $items[] = 'maybe';
-                }
+            if ($value !== 5) {
+                continue;
             }
+            if ($value2 !== 10) {
+                continue;
+            }
+
+            $items[] = 'maybe';
         }
     }
 }
```

<br><br>

### `ChangeNestedIfsToEarlyReturnRector`

- class: [`Rector\SOLID\Rector\If_\ChangeNestedIfsToEarlyReturnRector`](/rules/solid/src/Rector/If_/ChangeNestedIfsToEarlyReturnRector.php)
- [test fixtures](/rules/solid/tests/Rector/If_/ChangeNestedIfsToEarlyReturnRector/Fixture)

Change nested ifs to early return

```diff
 class SomeClass
 {
     public function run()
     {
-        if ($value === 5) {
-            if ($value2 === 10) {
-                return 'yes';
-            }
+        if ($value !== 5) {
+            return 'no';
+        }
+
+        if ($value2 === 10) {
+            return 'yes';
         }

         return 'no';
     }
 }
```

<br><br>

### `ChangeReadOnlyPropertyWithDefaultValueToConstantRector`

- class: [`Rector\SOLID\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector`](/rules/solid/src/Rector/Property/ChangeReadOnlyPropertyWithDefaultValueToConstantRector.php)
- [test fixtures](/rules/solid/tests/Rector/Property/ChangeReadOnlyPropertyWithDefaultValueToConstantRector/Fixture)

Change property with read only status with default value to constant

```diff
 class SomeClass
 {
     /**
      * @var string[]
      */
-    private $magicMethods = [
+    private const MAGIC_METHODS = [
         '__toString',
         '__wakeup',
     ];

     public function run()
     {
-        foreach ($this->magicMethods as $magicMethod) {
+        foreach (self::MAGIC_METHODS as $magicMethod) {
             echo $magicMethod;
         }
     }
 }
```

<br><br>

### `ChangeReadOnlyVariableWithDefaultValueToConstantRector`

- class: [`Rector\SOLID\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector`](/rules/solid/src/Rector/Class_/ChangeReadOnlyVariableWithDefaultValueToConstantRector.php)
- [test fixtures](/rules/solid/tests/Rector/Class_/ChangeReadOnlyVariableWithDefaultValueToConstantRector/Fixture)

Change variable with read only status with default value to constant

```diff
 class SomeClass
 {
+    /**
+     * @var string[]
+     */
+    private const REPLACEMENTS = [
+        'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
+        'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
+    ];
+
     public function run()
     {
-        $replacements = [
-            'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
-            'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
-        ];
-
-        foreach ($replacements as $class => $method) {
+        foreach (self::REPLACEMENTS as $class => $method) {
         }
     }
 }
```

<br><br>

### `FinalizeClassesWithoutChildrenRector`

- class: [`Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector`](/rules/solid/src/Rector/Class_/FinalizeClassesWithoutChildrenRector.php)
- [test fixtures](/rules/solid/tests/Rector/Class_/FinalizeClassesWithoutChildrenRector/Fixture)

Finalize every class that has no children

```diff
-class FirstClass
+final class FirstClass
 {
 }

 class SecondClass
 {
 }

-class ThirdClass extends SecondClass
+final class ThirdClass extends SecondClass
 {
 }
```

<br><br>

### `MakeUnusedClassesWithChildrenAbstractRector`

- class: [`Rector\SOLID\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector`](/rules/solid/src/Rector/Class_/MakeUnusedClassesWithChildrenAbstractRector.php)
- [test fixtures](/rules/solid/tests/Rector/Class_/MakeUnusedClassesWithChildrenAbstractRector/Fixture)

Classes that have no children nor are used, should have abstract

```diff
 class SomeClass extends PossibleAbstractClass
 {
 }

-class PossibleAbstractClass
+abstract class PossibleAbstractClass
 {
 }
```

<br><br>

### `MultiParentingToAbstractDependencyRector`

- class: [`Rector\SOLID\Rector\Class_\MultiParentingToAbstractDependencyRector`](/rules/solid/src/Rector/Class_/MultiParentingToAbstractDependencyRector.php)
- [test fixtures](/rules/solid/tests/Rector/Class_/MultiParentingToAbstractDependencyRector/Fixture)

Move dependency passed to all children to parent as @inject/@required dependency

```php
<?php

declare(strict_types=1);

use Rector\SOLID\Rector\Class_\MultiParentingToAbstractDependencyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MultiParentingToAbstractDependencyRector::class)
        ->call('configure', [[
            MultiParentingToAbstractDependencyRector::FRAMEWORK => 'nette'
        ]]);
};
```

↓

```diff
 abstract class AbstractParentClass
 {
-    private $someDependency;
-
-    public function __construct(SomeDependency $someDependency)
-    {
-        $this->someDependency = $someDependency;
-    }
+    /**
+     * @inject
+     * @var SomeDependency
+     */
+    public $someDependency;
 }

 class FirstChild extends AbstractParentClass
 {
-    public function __construct(SomeDependency $someDependency)
-    {
-        parent::__construct($someDependency);
-    }
 }

 class SecondChild extends AbstractParentClass
 {
-    public function __construct(SomeDependency $someDependency)
-    {
-        parent::__construct($someDependency);
-    }
 }
```

<br><br>

### `RemoveAlwaysElseRector`

- class: [`Rector\SOLID\Rector\If_\RemoveAlwaysElseRector`](/rules/solid/src/Rector/If_/RemoveAlwaysElseRector.php)
- [test fixtures](/rules/solid/tests/Rector/If_/RemoveAlwaysElseRector/Fixture)

Split if statement, when if condition always break execution flow

```diff
 class SomeClass
 {
     public function run($value)
     {
         if ($value) {
             throw new \InvalidStateException;
-        } else {
-            return 10;
         }
+
+        return 10;
     }
 }
```

<br><br>

### `RepeatedLiteralToClassConstantRector`

- class: [`Rector\SOLID\Rector\Class_\RepeatedLiteralToClassConstantRector`](/rules/solid/src/Rector/Class_/RepeatedLiteralToClassConstantRector.php)
- [test fixtures](/rules/solid/tests/Rector/Class_/RepeatedLiteralToClassConstantRector/Fixture)

Replace repeated strings with constant

```diff
 class SomeClass
 {
+    /**
+     * @var string
+     */
+    private const REQUIRES = 'requires';
     public function run($key, $items)
     {
-        if ($key === 'requires') {
-            return $items['requires'];
+        if ($key === self::REQUIRES) {
+            return $items[self::REQUIRES];
         }
     }
 }
```

<br><br>

### `UseInterfaceOverImplementationInConstructorRector`

- class: [`Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector`](/rules/solid/src/Rector/ClassMethod/UseInterfaceOverImplementationInConstructorRector.php)
- [test fixtures](/rules/solid/tests/Rector/ClassMethod/UseInterfaceOverImplementationInConstructorRector/Fixture)

Use interface instead of specific class

```diff
 class SomeClass
 {
-    public function __construct(SomeImplementation $someImplementation)
+    public function __construct(SomeInterface $someImplementation)
     {
     }
 }

 class SomeImplementation implements SomeInterface
 {
 }

 interface SomeInterface
 {
 }
```

<br><br>

## Sensio

### `RemoveServiceFromSensioRouteRector`

- class: [`Rector\Sensio\Rector\ClassMethod\RemoveServiceFromSensioRouteRector`](/rules/sensio/src/Rector/ClassMethod/RemoveServiceFromSensioRouteRector.php)
- [test fixtures](/rules/sensio/tests/Rector/ClassMethod/RemoveServiceFromSensioRouteRector/Fixture)

Remove service from Sensio @Route

```diff
 use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

 final class SomeClass
 {
     /**
-     * @Route(service="some_service")
+     * @Route()
      */
     public function run()
     {
     }
 }
```

<br><br>

### `ReplaceSensioRouteAnnotationWithSymfonyRector`

- class: [`Rector\Sensio\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector`](/rules/sensio/src/Rector/ClassMethod/ReplaceSensioRouteAnnotationWithSymfonyRector.php)
- [test fixtures](/rules/sensio/tests/Rector/ClassMethod/ReplaceSensioRouteAnnotationWithSymfonyRector/Fixture)

Replace Sensio @Route annotation with Symfony one

```diff
-use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
+use Symfony\Component\Routing\Annotation\Route;

 final class SomeClass
 {
     /**
      * @Route()
      */
     public function run()
     {
     }
 }
```

<br><br>

### `TemplateAnnotationToThisRenderRector`

- class: [`Rector\Sensio\Rector\ClassMethod\TemplateAnnotationToThisRenderRector`](/rules/sensio/src/Rector/ClassMethod/TemplateAnnotationToThisRenderRector.php)
- [test fixtures](/rules/sensio/tests/Rector/ClassMethod/TemplateAnnotationToThisRenderRector/Fixture)

Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony

```diff
-/**
- * @Template()
- */
 public function indexAction()
 {
+    return $this->render('index.html.twig');
 }
```

<br><br>

## StrictCodeQuality

### `VarInlineAnnotationToAssertRector`

- class: [`Rector\StrictCodeQuality\Rector\Stmt\VarInlineAnnotationToAssertRector`](/rules/strict-code-quality/src/Rector/Stmt/VarInlineAnnotationToAssertRector.php)
- [test fixtures](/rules/strict-code-quality/tests/Rector/Stmt/VarInlineAnnotationToAssertRector/Fixture)

Turn @var inline checks above code to `assert()` of the type

```diff
 class SomeClass
 {
     public function run()
     {
         /** @var SpecificClass $value */
+        assert($value instanceof SpecificClass);
         $value->call();
     }
 }
```

<br><br>

## Symfony

### `ActionSuffixRemoverRector`

- class: [`Rector\Symfony\Rector\ClassMethod\ActionSuffixRemoverRector`](/rules/symfony/src/Rector/ClassMethod/ActionSuffixRemoverRector.php)
- [test fixtures](/rules/symfony/tests/Rector/ClassMethod/ActionSuffixRemoverRector/Fixture)

Removes Action suffixes from methods in Symfony Controllers

```diff
 class SomeController
 {
-    public function indexAction()
+    public function index()
     {
     }
 }
```

<br><br>

### `AddFlashRector`

- class: [`Rector\Symfony\Rector\MethodCall\AddFlashRector`](/rules/symfony/src/Rector/MethodCall/AddFlashRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/AddFlashRector/Fixture)

Turns long flash adding to short helper method in Controller in Symfony

```diff
 class SomeController extends Controller
 {
     public function some(Request $request)
     {
-        $request->getSession()->getFlashBag()->add("success", "something");
+        $this->addFlash("success", "something");
     }
 }
```

<br><br>

### `CascadeValidationFormBuilderRector`

- class: [`Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector`](/rules/symfony/src/Rector/MethodCall/CascadeValidationFormBuilderRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/CascadeValidationFormBuilderRector/Fixture)

Change "cascade_validation" option to specific node attribute

```diff
 class SomeController
 {
     public function someMethod()
     {
-        $form = $this->createFormBuilder($article, ['cascade_validation' => true])
-            ->add('author', new AuthorType())
+        $form = $this->createFormBuilder($article)
+            ->add('author', new AuthorType(), [
+                'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
+            ])
             ->getForm();
     }

     protected function createFormBuilder()
     {
         return new FormBuilder();
     }
 }
```

<br><br>

### `ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector`

- class: [`Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector`](/rules/symfony/src/Rector/MethodCall/ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector/Fixture)

Rename `type` option to `entry_type` in CollectionType

```diff
 use Symfony\Component\Form\AbstractType;
 use Symfony\Component\Form\FormBuilderInterface;
 use Symfony\Component\Form\Extension\Core\Type\CollectionType;
 use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

 class TaskType extends AbstractType
 {
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         $builder->add('tags', CollectionType::class, [
-            'type' => ChoiceType::class,
-            'options' => [1, 2, 3],
+            'entry_type' => ChoiceType::class,
+            'entry_options' => [1, 2, 3],
         ]);
     }
 }
```

<br><br>

### `ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector`

- class: [`Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector`](/rules/symfony/src/Rector/MethodCall/ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector/Fixture)

Change type in CollectionType from alias string to class reference

```diff
 use Symfony\Component\Form\AbstractType;
 use Symfony\Component\Form\FormBuilderInterface;
 use Symfony\Component\Form\Extension\Core\Type\CollectionType;

 class TaskType extends AbstractType
 {
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         $builder->add('tags', CollectionType::class, [
-            'type' => 'choice',
+            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
         ]);

         $builder->add('tags', 'collection', [
-            'type' => 'choice',
+            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
         ]);
     }
 }
```

<br><br>

### `ChangeFileLoaderInExtensionAndKernelRector`

- class: [`Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector`](/rules/symfony/src/Rector/Class_/ChangeFileLoaderInExtensionAndKernelRector.php)
- [test fixtures](/rules/symfony/tests/Rector/Class_/ChangeFileLoaderInExtensionAndKernelRector/Fixture)

Change XML loader to YAML in Bundle Extension

```php
<?php

declare(strict_types=1);

use Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeFileLoaderInExtensionAndKernelRector::class)
        ->call(
            'configure',
            [[
                ChangeFileLoaderInExtensionAndKernelRector::FROM => 'xml',
                ChangeFileLoaderInExtensionAndKernelRector::TO => 'yaml',
            ]]
        );
};
```

↓

```diff
 use Symfony\Component\Config\FileLocator;
 use Symfony\Component\DependencyInjection\ContainerBuilder;
-use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
+use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
 use Symfony\Component\HttpKernel\DependencyInjection\Extension;

 final class SomeExtension extends Extension
 {
     public function load(array $configs, ContainerBuilder $container)
     {
-        $loader = new XmlFileLoader($container, new FileLocator());
-        $loader->load(__DIR__ . '/../Resources/config/controller.xml');
-        $loader->load(__DIR__ . '/../Resources/config/events.xml');
+        $loader = new YamlFileLoader($container, new FileLocator());
+        $loader->load(__DIR__ . '/../Resources/config/controller.yaml');
+        $loader->load(__DIR__ . '/../Resources/config/events.yaml');
     }
 }
```

<br><br>

### `ConsoleExceptionToErrorEventConstantRector`

- class: [`Rector\Symfony\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector`](/rules/symfony/src/Rector/ClassConstFetch/ConsoleExceptionToErrorEventConstantRector.php)
- [test fixtures](/rules/symfony/tests/Rector/ClassConstFetch/ConsoleExceptionToErrorEventConstantRector/Fixture)

Turns old event name with EXCEPTION to ERROR constant in Console in Symfony

```diff
-"console.exception"
+Symfony\Component\Console\ConsoleEvents::ERROR
```
```diff
-Symfony\Component\Console\ConsoleEvents::EXCEPTION
+Symfony\Component\Console\ConsoleEvents::ERROR
```

<br><br>

### `ConsoleExecuteReturnIntRector`

- class: [`Rector\Symfony\Rector\ClassMethod\ConsoleExecuteReturnIntRector`](/rules/symfony/src/Rector/ClassMethod/ConsoleExecuteReturnIntRector.php)
- [test fixtures](/rules/symfony/tests/Rector/ClassMethod/ConsoleExecuteReturnIntRector/Fixture)

Returns int from Command::execute command

```diff
 class SomeCommand extends Command
 {
-    public function execute(InputInterface $input, OutputInterface $output)
+    public function execute(InputInterface $input, OutputInterface $output): int
     {
-        return null;
+        return 0;
     }
 }
```

<br><br>

### `ConstraintUrlOptionRector`

- class: [`Rector\Symfony\Rector\ConstFetch\ConstraintUrlOptionRector`](/rules/symfony/src/Rector/ConstFetch/ConstraintUrlOptionRector.php)
- [test fixtures](/rules/symfony/tests/Rector/ConstFetch/ConstraintUrlOptionRector/Fixture)

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

<br><br>

### `ContainerBuilderCompileEnvArgumentRector`

- class: [`Rector\Symfony\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector`](/rules/symfony/src/Rector/MethodCall/ContainerBuilderCompileEnvArgumentRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/ContainerBuilderCompileEnvArgumentRector/Fixture)

Turns old default value to parameter in `ContainerBuilder->build()` method in DI in Symfony

```diff
 use Symfony\Component\DependencyInjection\ContainerBuilder;

 $containerBuilder = new ContainerBuilder();
-$containerBuilder->compile();
+$containerBuilder->compile(true);
```

<br><br>

### `ContainerGetToConstructorInjectionRector`

- class: [`Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector`](/rules/symfony/src/Rector/MethodCall/ContainerGetToConstructorInjectionRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/ContainerGetToConstructorInjectionRector/Fixture)

Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony

```php
<?php

declare(strict_types=1);

use Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ContainerGetToConstructorInjectionRector::class)
        ->call(
            'configure',
            [[
                ContainerGetToConstructorInjectionRector::CONTAINER_AWARE_PARENT_TYPES => [
                    'ContainerAwareParentClassName',
                    'ContainerAwareParentCommandClassName',
                    'ThisClassCallsMethodInConstructorClassName',
                ],
            ]]
        );
};
```

↓

```diff
-final class SomeCommand extends ContainerAwareCommand
+final class SomeCommand extends Command
 {
+    public function __construct(SomeService $someService)
+    {
+        $this->someService = $someService;
+    }
+
     public function someMethod()
     {
         // ...
-        $this->getContainer()->get('some_service');
-        $this->container->get('some_service');
+        $this->someService;
+        $this->someService;
     }
 }
```

<br><br>

### `FormIsValidRector`

- class: [`Rector\Symfony\Rector\MethodCall\FormIsValidRector`](/rules/symfony/src/Rector/MethodCall/FormIsValidRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/FormIsValidRector/Fixture)

Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony

```diff
-if ($form->isValid()) {
+if ($form->isSubmitted() && $form->isValid()) {
 }
```

<br><br>

### `FormTypeGetParentRector`

- class: [`Rector\Symfony\Rector\ClassMethod\FormTypeGetParentRector`](/rules/symfony/src/Rector/ClassMethod/FormTypeGetParentRector.php)
- [test fixtures](/rules/symfony/tests/Rector/ClassMethod/FormTypeGetParentRector/Fixture)

Turns string Form Type references to their `CONSTANT` alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony

```diff
 use Symfony\Component\Form\AbstractType;

 class SomeType extends AbstractType
 {
     public function getParent()
     {
-        return 'collection';
+        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
     }
 }
```
```diff
 use Symfony\Component\Form\AbstractTypeExtension;

 class SomeExtension extends AbstractTypeExtension
 {
     public function getExtendedType()
     {
-        return 'collection';
+        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
     }
 }
```

<br><br>

### `FormTypeInstanceToClassConstRector`

- class: [`Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector`](/rules/symfony/src/Rector/MethodCall/FormTypeInstanceToClassConstRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/FormTypeInstanceToClassConstRector/Fixture)

Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"

```diff
 class SomeController
 {
     public function action()
     {
-        $form = $this->createForm(new TeamType, $entity);
+        $form = $this->createForm(TeamType::class, $entity);
     }
 }
```

<br><br>

### `GetParameterToConstructorInjectionRector`

- class: [`Rector\Symfony\Rector\MethodCall\GetParameterToConstructorInjectionRector`](/rules/symfony/src/Rector/MethodCall/GetParameterToConstructorInjectionRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/GetParameterToConstructorInjectionRector/Fixture)

Turns fetching of parameters via `getParameter()` in ContainerAware to constructor injection in Command and Controller in Symfony

```diff
-class MyCommand extends ContainerAwareCommand
+class MyCommand extends Command
 {
+    private $someParameter;
+
+    public function __construct($someParameter)
+    {
+        $this->someParameter = $someParameter;
+    }
+
     public function someMethod()
     {
-        $this->getParameter('someParameter');
+        $this->someParameter;
     }
 }
```

<br><br>

### `GetRequestRector`

- class: [`Rector\Symfony\Rector\ClassMethod\GetRequestRector`](/rules/symfony/src/Rector/ClassMethod/GetRequestRector.php)
- [test fixtures](/rules/symfony/tests/Rector/ClassMethod/GetRequestRector/Fixture)

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

```diff
+use Symfony\Component\HttpFoundation\Request;
+
 class SomeController
 {
-    public function someAction()
+    public function someAction(Request $request)
     {
-        $this->getRequest()->...();
+        $request->...();
     }
 }
```

<br><br>

### `GetToConstructorInjectionRector`

- class: [`Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector`](/rules/symfony/src/Rector/MethodCall/GetToConstructorInjectionRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/GetToConstructorInjectionRector/Fixture)

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

```php
<?php

declare(strict_types=1);

use Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetToConstructorInjectionRector::class)
        ->call(
            'configure',
            [[
                GetToConstructorInjectionRector::GET_METHOD_AWARE_TYPES => [
                    'SymfonyControllerClassName',
                    'GetTraitClassName',
                ],
            ]]
        );
};
```

↓

```diff
-class MyCommand extends ContainerAwareCommand
+class MyCommand extends Command
 {
+    public function __construct(SomeService $someService)
+    {
+        $this->someService = $someService;
+    }
+
     public function someMethod()
     {
-        // ...
-        $this->get('some_service');
+        $this->someService;
     }
 }
```

<br><br>

### `MakeCommandLazyRector`

- class: [`Rector\Symfony\Rector\Class_\MakeCommandLazyRector`](/rules/symfony/src/Rector/Class_/MakeCommandLazyRector.php)
- [test fixtures](/rules/symfony/tests/Rector/Class_/MakeCommandLazyRector/Fixture)

Make Symfony commands lazy

```diff
 use Symfony\Component\Console\Command\Command

 class SunshineCommand extends Command
 {
+    protected static $defaultName = 'sunshine';
     public function configure()
     {
-        $this->setName('sunshine');
     }
 }
```

<br><br>

### `MakeDispatchFirstArgumentEventRector`

- class: [`Rector\Symfony\Rector\MethodCall\MakeDispatchFirstArgumentEventRector`](/rules/symfony/src/Rector/MethodCall/MakeDispatchFirstArgumentEventRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/MakeDispatchFirstArgumentEventRector/Fixture)

Make event object a first argument of `dispatch()` method, event name as second

```diff
 use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

 class SomeClass
 {
     public function run(EventDispatcherInterface $eventDispatcher)
     {
-        $eventDispatcher->dispatch('event_name', new Event());
+        $eventDispatcher->dispatch(new Event(), 'event_name');
     }
 }
```

<br><br>

### `MergeMethodAnnotationToRouteAnnotationRector`

- class: [`Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector`](/rules/symfony/src/Rector/ClassMethod/MergeMethodAnnotationToRouteAnnotationRector.php)
- [test fixtures](/rules/symfony/tests/Rector/ClassMethod/MergeMethodAnnotationToRouteAnnotationRector/Fixture)

Merge removed @Method annotation to @Route one

```diff
-use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
 use Symfony\Component\Routing\Annotation\Route;

 class DefaultController extends Controller
 {
     /**
-     * @Route("/show/{id}")
-     * @Method({"GET", "HEAD"})
+     * @Route("/show/{id}", methods={"GET","HEAD"})
      */
     public function show($id)
     {
     }
 }
```

<br><br>

### `OptionNameRector`

- class: [`Rector\Symfony\Rector\MethodCall\OptionNameRector`](/rules/symfony/src/Rector/MethodCall/OptionNameRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/OptionNameRector/Fixture)

Turns old option names to new ones in FormTypes in Form in Symfony

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

<br><br>

### `ParseFileRector`

- class: [`Rector\Symfony\Rector\StaticCall\ParseFileRector`](/rules/symfony/src/Rector/StaticCall/ParseFileRector.php)
- [test fixtures](/rules/symfony/tests/Rector/StaticCall/ParseFileRector/Fixture)

session > use_strict_mode is true by default and can be removed

```diff
-session > use_strict_mode: true
+session:
```

<br><br>

### `ProcessBuilderGetProcessRector`

- class: [`Rector\Symfony\Rector\MethodCall\ProcessBuilderGetProcessRector`](/rules/symfony/src/Rector/MethodCall/ProcessBuilderGetProcessRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/ProcessBuilderGetProcessRector/Fixture)

Removes `$processBuilder->getProcess()` calls to `$processBuilder` in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.

```diff
 $processBuilder = new Symfony\Component\Process\ProcessBuilder;
-$process = $processBuilder->getProcess();
-$commamdLine = $processBuilder->getProcess()->getCommandLine();
+$process = $processBuilder;
+$commamdLine = $processBuilder->getCommandLine();
```

<br><br>

### `ProcessBuilderInstanceRector`

- class: [`Rector\Symfony\Rector\StaticCall\ProcessBuilderInstanceRector`](/rules/symfony/src/Rector/StaticCall/ProcessBuilderInstanceRector.php)
- [test fixtures](/rules/symfony/tests/Rector/StaticCall/ProcessBuilderInstanceRector/Fixture)

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

<br><br>

### `ReadOnlyOptionToAttributeRector`

- class: [`Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector`](/rules/symfony/src/Rector/MethodCall/ReadOnlyOptionToAttributeRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/ReadOnlyOptionToAttributeRector/Fixture)

Change "read_only" option in form to attribute

```diff
 use Symfony\Component\Form\FormBuilderInterface;

 function buildForm(FormBuilderInterface $builder, array $options)
 {
-    $builder->add('cuid', TextType::class, ['read_only' => true]);
+    $builder->add('cuid', TextType::class, ['attr' => ['read_only' => true]]);
 }
```

<br><br>

### `RedirectToRouteRector`

- class: [`Rector\Symfony\Rector\MethodCall\RedirectToRouteRector`](/rules/symfony/src/Rector/MethodCall/RedirectToRouteRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/RedirectToRouteRector/Fixture)

Turns redirect to route to short helper method in Controller in Symfony

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

<br><br>

### `RemoveDefaultGetBlockPrefixRector`

- class: [`Rector\Symfony\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector`](/rules/symfony/src/Rector/ClassMethod/RemoveDefaultGetBlockPrefixRector.php)
- [test fixtures](/rules/symfony/tests/Rector/ClassMethod/RemoveDefaultGetBlockPrefixRector/Fixture)

Rename `getBlockPrefix()` if it returns the default value - class to underscore, e.g. UserFormType = user_form

```diff
 use Symfony\Component\Form\AbstractType;

 class TaskType extends AbstractType
 {
-    public function getBlockPrefix()
-    {
-        return 'task';
-    }
 }
```

<br><br>

### `ResponseStatusCodeRector`

- class: [`Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector`](/rules/symfony/src/Rector/BinaryOp/ResponseStatusCodeRector.php)
- [test fixtures](/rules/symfony/tests/Rector/BinaryOp/ResponseStatusCodeRector/Fixture)

Turns status code numbers to constants

```diff
 class SomeController
 {
     public function index()
     {
         $response = new \Symfony\Component\HttpFoundation\Response();
-        $response->setStatusCode(200);
+        $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_OK);

-        if ($response->getStatusCode() === 200) {}
+        if ($response->getStatusCode() === \Symfony\Component\HttpFoundation\Response::HTTP_OK) {}
     }
 }
```

<br><br>

### `RootNodeTreeBuilderRector`

- class: [`Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector`](/rules/symfony/src/Rector/New_/RootNodeTreeBuilderRector.php)
- [test fixtures](/rules/symfony/tests/Rector/New_/RootNodeTreeBuilderRector/Fixture)

Changes  Process string argument to an array

```diff
 use Symfony\Component\Config\Definition\Builder\TreeBuilder;

-$treeBuilder = new TreeBuilder();
-$rootNode = $treeBuilder->root('acme_root');
+$treeBuilder = new TreeBuilder('acme_root');
+$rootNode = $treeBuilder->getRootNode();
 $rootNode->someCall();
```

<br><br>

### `SimplifyWebTestCaseAssertionsRector`

- class: [`Rector\Symfony\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector`](/rules/symfony/src/Rector/MethodCall/SimplifyWebTestCaseAssertionsRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/SimplifyWebTestCaseAssertionsRector/Fixture)

Simplify use of assertions in WebTestCase

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
 {
     public function test()
     {
-        $this->assertSame(200, $client->getResponse()->getStatusCode());
+         $this->assertResponseIsSuccessful();
     }

     public function testUrl()
     {
-        $this->assertSame(301, $client->getResponse()->getStatusCode());
-        $this->assertSame('https://example.com', $client->getResponse()->headers->get('Location'));
+        $this->assertResponseRedirects('https://example.com', 301);
     }

     public function testContains()
     {
-        $this->assertContains('Hello World', $crawler->filter('h1')->text());
+        $this->assertSelectorTextContains('h1', 'Hello World');
     }
 }
```

<br><br>

### `StringFormTypeToClassRector`

- class: [`Rector\Symfony\Rector\MethodCall\StringFormTypeToClassRector`](/rules/symfony/src/Rector/MethodCall/StringFormTypeToClassRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/StringFormTypeToClassRector/Fixture)

Turns string Form Type references to their `CONSTANT` alternatives in FormTypes in Form in Symfony. To enable custom types, add `link` to your container XML `dump` in "parameters > symfony_container_xml_path"

```diff
 $formBuilder = new Symfony\Component\Form\FormBuilder;
-$formBuilder->add('name', 'form.type.text');
+$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

<br><br>

### `StringToArrayArgumentProcessRector`

- class: [`Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector`](/rules/symfony/src/Rector/New_/StringToArrayArgumentProcessRector.php)
- [test fixtures](/rules/symfony/tests/Rector/New_/StringToArrayArgumentProcessRector/Fixture)

Changes Process string argument to an array

```diff
 use Symfony\Component\Process\Process;
-$process = new Process('ls -l');
+$process = new Process(['ls', '-l']);
```

<br><br>

### `VarDumperTestTraitMethodArgsRector`

- class: [`Rector\Symfony\Rector\MethodCall\VarDumperTestTraitMethodArgsRector`](/rules/symfony/src/Rector/MethodCall/VarDumperTestTraitMethodArgsRector.php)
- [test fixtures](/rules/symfony/tests/Rector/MethodCall/VarDumperTestTraitMethodArgsRector/Fixture)

Adds a new `$filter` argument in `VarDumperTestTrait->assertDumpEquals()` and `VarDumperTestTrait->assertDumpMatchesFormat()` in Validator in Symfony.

```diff
-$varDumperTestTrait->assertDumpEquals($dump, $data, $message = "");
+$varDumperTestTrait->assertDumpEquals($dump, $data, $filter = 0, $message = "");
```
```diff
-$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $message = "");
+$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $filter = 0, $message = "");
```

<br><br>

## SymfonyCodeQuality

### `EventListenerToEventSubscriberRector`

- class: [`Rector\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector`](/rules/symfony-code-quality/src/Rector/Class_/EventListenerToEventSubscriberRector.php)
- [test fixtures](/rules/symfony-code-quality/tests/Rector/Class_/EventListenerToEventSubscriberRector/Fixture)

Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file

```diff
 <?php

-class SomeListener
+use Symfony\Component\EventDispatcher\EventSubscriberInterface;
+
+class SomeEventSubscriber implements EventSubscriberInterface
 {
+     /**
+      * @return string[]
+      */
+     public static function getSubscribedEvents(): array
+     {
+         return ['some_event' => 'methodToBeCalled'];
+     }
+
      public function methodToBeCalled()
      {
      }
-}
-
-// in config.yaml
-services:
-    SomeListener:
-        tags:
-            - { name: kernel.event_listener, event: 'some_event', method: 'methodToBeCalled' }
+}
```

<br><br>

## SymfonyPHPUnit

### `SelfContainerGetMethodCallFromTestToSetUpMethodRector`

- class: [`Rector\SymfonyPHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector`](/rules/symfony-phpunit/src/Rector/Class_/SelfContainerGetMethodCallFromTestToSetUpMethodRector.php)
- [test fixtures](/rules/symfony-phpunit/tests/Rector/Class_/SelfContainerGetMethodCallFromTestToSetUpMethodRector/Fixture)

Move self::$container service fetching from test methods up to setUp method

```diff
 use ItemRepository;
 use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

 class SomeTest extends KernelTestCase
 {
+    /**
+     * @var \ItemRepository
+     */
+    private $itemRepository;
+
+    protected function setUp()
+    {
+        parent::setUp();
+        $this->itemRepository = self::$container->get(ItemRepository::class);
+    }
+
     public function testOne()
     {
-        $itemRepository = self::$container->get(ItemRepository::class);
-        $itemRepository->doStuff();
+        $this->itemRepository->doStuff();
     }

     public function testTwo()
     {
-        $itemRepository = self::$container->get(ItemRepository::class);
-        $itemRepository->doAnotherStuff();
+        $this->itemRepository->doAnotherStuff();
     }
 }
```

<br><br>

## SymfonyPhpConfig

### `ChangeServiceArgumentsToMethodCallRector`

- class: [`Rector\SymfonyPhpConfig\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector`](/rules/symfony-php-config/src/Rector/MethodCall/ChangeServiceArgumentsToMethodCallRector.php)
- [test fixtures](/rules/symfony-php-config/tests/Rector/MethodCall/ChangeServiceArgumentsToMethodCallRector/Fixture)

Change `$service->arg(...)` to `$service->call(...)`

```php
<?php

declare(strict_types=1);

use Rector\SymfonyPhpConfig\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeServiceArgumentsToMethodCallRector::class)
        ->call(
            'configure',
            [[
                ChangeServiceArgumentsToMethodCallRector::CLASS_TYPE_TO_METHOD_NAME => [
                    'SomeClass' => 'configure',
                ],
            ]]
        );
};
```

↓

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();

     $services->set(SomeClass::class)
-        ->arg('$key', 'value');
+        ->call('configure', [[
+            '$key' => 'value
+        ]]);
 }
```

<br><br>

### `ReplaceArrayWithObjectRector`

- class: [`Rector\SymfonyPhpConfig\Rector\ArrayItem\ReplaceArrayWithObjectRector`](/rules/symfony-php-config/src/Rector/ArrayItem/ReplaceArrayWithObjectRector.php)
- [test fixtures](/rules/symfony-php-config/tests/Rector/ArrayItem/ReplaceArrayWithObjectRector/Fixture)

Replace complex array configuration in configs with value object

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\SymfonyPhpConfig\Rector\ArrayItem\ReplaceArrayWithObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplaceArrayWithObjectRector::class)
        ->call(
            'configure',
            [[
                ReplaceArrayWithObjectRector::CONSTANT_NAMES_TO_VALUE_OBJECTS => [
                    RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => MethodCallRename::class,
                ],
            ]]
        );
};
```

↓

```diff
 use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();

     $services->set(RenameMethodRector::class)
         ->call('configure', [[
-            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
-                'Illuminate\Auth\Access\Gate' => [
-                    'access' => 'inspect',
-                ]
-            ]]
-        ]);
+            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => \Rector\SymfonyPhpConfig\inline_value_objects([
+                new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\Auth\Access\Gate', 'access', 'inspect'),
+            ])
+        ]]);
 }
```

<br><br>

## Transform

### `ArgumentFuncCallToMethodCallRector`

- class: [`Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector`](/rules/transform/src/Rector/FuncCall/ArgumentFuncCallToMethodCallRector.php)
- [test fixtures](/rules/transform/tests/Rector/FuncCall/ArgumentFuncCallToMethodCallRector/Fixture)

Move help facade-like function calls to constructor injection

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentFuncCallToMethodCallRector::class)
        ->call(
            'configure',
            [[
                ArgumentFuncCallToMethodCallRector::FUNCTIONS_TO_METHOD_CALLS => inline_value_objects(
                                [new ArgumentFuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', null, 'make')]
                            )
            ]]
        );
};
```

↓

```diff
 class SomeController
 {
+    /**
+     * @var \Illuminate\Contracts\View\Factory
+     */
+    private $viewFactory;
+
+    public function __construct(\Illuminate\Contracts\View\Factory $viewFactory)
+    {
+        $this->viewFactory = $viewFactory;
+    }
+
     public function action()
     {
-        $template = view('template.blade');
-        $viewFactory = view();
+        $template = $this->viewFactory->make('template.blade');
+        $viewFactory = $this->viewFactory;
     }
 }
```

<br><br>

### `FuncCallToMethodCallRector`

- class: [`Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector`](/rules/transform/src/Rector/FuncCall/FuncCallToMethodCallRector.php)
- [test fixtures](/rules/transform/tests/Rector/FuncCall/FuncCallToMethodCallRector/Fixture)

Turns defined function calls to local method calls.

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\ValueObject\FuncNameToMethodCallName;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToMethodCallRector::class)
        ->call(
            'configure',
            [[
                FuncCallToMethodCallRector::FUNC_CALL_TO_CLASS_METHOD_CALL => inline_value_objects(
                                [new FuncNameToMethodCallName('view', 'Namespaced\SomeRenderer', 'render')]
                            )
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
+    /**
+     * @var \Namespaced\SomeRenderer
+     */
+    private $someRenderer;
+
+    public function __construct(\Namespaced\SomeRenderer $someRenderer)
+    {
+        $this->someRenderer = $someRenderer;
+    }
+
     public function run()
     {
-        view('...');
+        $this->someRenderer->view('...');
     }
 }
```

<br><br>

### `MethodCallToAnotherMethodCallWithArgumentsRector`

- class: [`Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector`](/rules/transform/src/Rector/MethodCall/MethodCallToAnotherMethodCallWithArgumentsRector.php)
- [test fixtures](/rules/transform/tests/Rector/MethodCall/MethodCallToAnotherMethodCallWithArgumentsRector/Fixture)

Turns old method call with specific types to new one with arguments

```php
<?php

declare(strict_types=1);

use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToAnotherMethodCallWithArgumentsRector::class)
        ->call(
            'configure',
            [[
                MethodCallToAnotherMethodCallWithArgumentsRector::METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS => inline_value_objects(
                                [new MethodCallRenameWithArrayKey('Nette\DI\ServiceDefinition', 'setInject', 'addTag', 'inject')]
                            )
            ]]
        );
};
```

↓

```diff
 $serviceDefinition = new Nette\DI\ServiceDefinition;
-$serviceDefinition->setInject();
+$serviceDefinition->addTag('inject');
```

<br><br>

### `MethodCallToPropertyFetchRector`

- class: [`Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector`](/rules/transform/src/Rector/MethodCall/MethodCallToPropertyFetchRector.php)
- [test fixtures](/rules/transform/tests/Rector/MethodCall/MethodCallToPropertyFetchRector/Fixture)

Turns method call "$this->something()" to property fetch "$this->something"

```php
<?php

declare(strict_types=1);

use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToPropertyFetchRector::class)
        ->call(
            'configure',
            [[
                MethodCallToPropertyFetchRector::METHOD_CALL_TO_PROPERTY_FETCHES => [
                    'someMethod' => 'someProperty',
                ],
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $this->someMethod();
+        $this->someProperty;
     }
 }
```

<br><br>

### `MethodCallToStaticCallRector`

- class: [`Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector`](/rules/transform/src/Rector/MethodCall/MethodCallToStaticCallRector.php)
- [test fixtures](/rules/transform/tests/Rector/MethodCall/MethodCallToStaticCallRector/Fixture)

Change method call to desired static call

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToStaticCallRector::class)
        ->call(
            'configure',
            [[
                MethodCallToStaticCallRector::METHOD_CALLS_TO_STATIC_CALLS => inline_value_objects(
                                [new MethodCallToStaticCall('AnotherDependency', 'process', 'StaticCaller', 'anotherMethod')]
                            )
            ]]
        );
};
```

↓

```diff
 final class SomeClass
 {
     private $anotherDependency;

     public function __construct(AnotherDependency $anotherDependency)
     {
         $this->anotherDependency = $anotherDependency;
     }

     public function loadConfiguration()
     {
-        return $this->anotherDependency->process('value');
+        return StaticCaller::anotherMethod('value');
     }
 }
```

<br><br>

### `NewToStaticCallRector`

- class: [`Rector\Transform\Rector\New_\NewToStaticCallRector`](/rules/transform/src/Rector/New_/NewToStaticCallRector.php)
- [test fixtures](/rules/transform/tests/Rector/New_/NewToStaticCallRector/Fixture)

Change new Object to static call

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\NewToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewToStaticCallRector::class)
        ->call(
            'configure',
            [[
                NewToStaticCallRector::TYPE_TO_STATIC_CALLS => inline_value_objects(
                                [new NewToStaticCall('Cookie', 'Cookie', 'create')]
                            )
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        new Cookie($name);
+        Cookie::create($name);
     }
 }
```

<br><br>

### `PropertyAssignToMethodCallRector`

- class: [`Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector`](/rules/transform/src/Rector/Assign/PropertyAssignToMethodCallRector.php)
- [test fixtures](/rules/transform/tests/Rector/Assign/PropertyAssignToMethodCallRector/Fixture)

Turns property assign of specific type and property name to method call

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector;
use Rector\Transform\ValueObject\PropertyAssignToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyAssignToMethodCallRector::class)
        ->call(
            'configure',
            [[
                PropertyAssignToMethodCallRector::PROPERTY_ASSIGNS_TO_METHODS_CALLS => inline_value_objects(
                                [new PropertyAssignToMethodCall('SomeClass', 'oldProperty', 'newMethodCall')]
                            )
            ]]
        );
};
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->oldProperty = false;
+$someObject->newMethodCall(false);
```

<br><br>

### `PropertyToMethodRector`

- class: [`Rector\Transform\Rector\Assign\PropertyToMethodRector`](/rules/transform/src/Rector/Assign/PropertyToMethodRector.php)
- [test fixtures](/rules/transform/tests/Rector/Assign/PropertyToMethodRector/Fixture)

Replaces properties assign calls be defined methods.

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\Assign\PropertyToMethodRector;
use Rector\Transform\ValueObject\PropertyToMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyToMethodRector::class)
        ->call(
            'configure',
            [[
                PropertyToMethodRector::PROPERTIES_TO_METHOD_CALLS => inline_value_objects(
                                [new PropertyToMethod('SomeObject', 'property', 'getProperty', [], 'setProperty')]
                            )
            ]]
        );
};
```

↓

```diff
-$result = $object->property;
-$object->property = $value;
+$result = $object->getProperty();
+$object->setProperty($value);
```
```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\Assign\PropertyToMethodRector;
use Rector\Transform\ValueObject\PropertyToMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyToMethodRector::class)
        ->call(
            'configure',
            [[
                PropertyToMethodRector::PROPERTIES_TO_METHOD_CALLS => inline_value_objects(
                                [new PropertyToMethod('SomeObject', 'property', 'getConfig', ['someArg'], null)]
                            )
            ]]
        );
};
```

↓

```diff
-$result = $object->property;
+$result = $object->getProperty('someArg');
```

<br><br>

### `ServiceGetterToConstructorInjectionRector`

- class: [`Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector`](/rules/transform/src/Rector/MethodCall/ServiceGetterToConstructorInjectionRector.php)
- [test fixtures](/rules/transform/tests/Rector/MethodCall/ServiceGetterToConstructorInjectionRector/Fixture)

Get service call to constructor injection

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->call(
            'configure',
            [[
                ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => inline_value_objects(
                                [new ServiceGetterToConstructorInjection('FirstService', 'getAnotherService', 'AnotherService')]
                            )
            ]]
        );
};
```

↓

```diff
 final class SomeClass
 {
     /**
      * @var FirstService
      */
     private $firstService;

-    public function __construct(FirstService $firstService)
-    {
-        $this->firstService = $firstService;
-    }
-
-    public function run()
-    {
-        $anotherService = $this->firstService->getAnotherService();
-        $anotherService->run();
-    }
-}
-
-class FirstService
-{
     /**
      * @var AnotherService
      */
     private $anotherService;

-    public function __construct(AnotherService $anotherService)
+    public function __construct(FirstService $firstService, AnotherService $anotherService)
     {
+        $this->firstService = $firstService;
         $this->anotherService = $anotherService;
     }

-    public function getAnotherService(): AnotherService
+    public function run()
     {
-         return $this->anotherService;
+        $anotherService = $this->anotherService;
+        $anotherService->run();
     }
 }
```

<br><br>

### `StaticCallToFuncCallRector`

- class: [`Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector`](/rules/transform/src/Rector/StaticCall/StaticCallToFuncCallRector.php)
- [test fixtures](/rules/transform/tests/Rector/StaticCall/StaticCallToFuncCallRector/Fixture)

Turns static call to function call.

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticCallToFuncCallRector::class)
        ->call(
            'configure',
            [[
                StaticCallToFuncCallRector::STATIC_CALLS_TO_FUNCTIONS => inline_value_objects(
                                [new StaticCallToFuncCall('OldClass', 'oldMethod', 'new_function')]
                            )
            ]]
        );
};
```

↓

```diff
-OldClass::oldMethod("args");
+new_function("args");
```

<br><br>

### `StaticCallToMethodCallRector`

- class: [`Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector`](/rules/transform/src/Rector/StaticCall/StaticCallToMethodCallRector.php)
- [test fixtures](/rules/transform/tests/Rector/StaticCall/StaticCallToMethodCallRector/Fixture)

Change static call to service method via constructor injection

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticCallToMethodCallRector::class)
        ->call(
            'configure',
            [[
                StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => inline_value_objects(
                                [new StaticCallToMethodCall(
                                    'Nette\Utils\FileSystem',
                                    'write',
                                    'Symplify\SmartFileSystem\SmartFileSystem',
                                    'dumpFile'
                                )]
                            )
            ]]
        );
};
```

↓

```diff
-use Nette\Utils\FileSystem;
+use Symplify\SmartFileSystem\SmartFileSystem;

 class SomeClass
 {
+    /**
+     * @var SmartFileSystem
+     */
+    private $smartFileSystem;
+
+    public function __construct(SmartFileSystem $smartFileSystem)
+    {
+        $this->smartFileSystem = $smartFileSystem;
+    }
+
     public function run()
     {
-        return FileSystem::write('file', 'content');
+        return $this->smartFileSystem->dumpFile('file', 'content');
     }
 }
```

<br><br>

## Twig

### `SimpleFunctionAndFilterRector`

- class: [`Rector\Twig\Rector\Return_\SimpleFunctionAndFilterRector`](/rules/twig/src/Rector/Return_/SimpleFunctionAndFilterRector.php)
- [test fixtures](/rules/twig/tests/Rector/Return_/SimpleFunctionAndFilterRector/Fixture)

Changes Twig_Function_Method to Twig_SimpleFunction calls in Twig_Extension.

```diff
 class SomeExtension extends Twig_Extension
 {
     public function getFunctions()
     {
         return [
-            'is_mobile' => new Twig_Function_Method($this, 'isMobile'),
+             new Twig_SimpleFunction('is_mobile', [$this, 'isMobile']),
         ];
     }

     public function getFilters()
     {
         return [
-            'is_mobile' => new Twig_Filter_Method($this, 'isMobile'),
+             new Twig_SimpleFilter('is_mobile', [$this, 'isMobile']),
         ];
     }
 }
```

<br><br>

## TypeDeclaration

### `AddArrayParamDocTypeRector`

- class: [`Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector`](/rules/type-declaration/src/Rector/ClassMethod/AddArrayParamDocTypeRector.php)
- [test fixtures](/rules/type-declaration/tests/Rector/ClassMethod/AddArrayParamDocTypeRector/Fixture)

Adds @param annotation to array parameters inferred from the rest of the code

```diff
 class SomeClass
 {
     /**
      * @var int[]
      */
     private $values;

+    /**
+     * @param int[] $values
+     */
     public function __construct(array $values)
     {
         $this->values = $values;
     }
 }
```

<br><br>

### `AddArrayReturnDocTypeRector`

- class: [`Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector`](/rules/type-declaration/src/Rector/ClassMethod/AddArrayReturnDocTypeRector.php)
- [test fixtures](/rules/type-declaration/tests/Rector/ClassMethod/AddArrayReturnDocTypeRector/Fixture)

Adds @return annotation to array parameters inferred from the rest of the code

```diff
 class SomeClass
 {
     /**
      * @var int[]
      */
     private $values;

+    /**
+     * @return int[]
+     */
     public function getValues(): array
     {
         return $this->values;
     }
 }
```

<br><br>

### `AddClosureReturnTypeRector`

- class: [`Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector`](/rules/type-declaration/src/Rector/Closure/AddClosureReturnTypeRector.php)
- [test fixtures](/rules/type-declaration/tests/Rector/Closure/AddClosureReturnTypeRector/Fixture)

Add known return type to functions

```diff
 class SomeClass
 {
     public function run($meetups)
     {
-        return array_filter($meetups, function (Meetup $meetup) {
+        return array_filter($meetups, function (Meetup $meetup): bool {
             return is_object($meetup);
         });
     }
 }
```

<br><br>

### `AddMethodCallBasedParamTypeRector`

- class: [`Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedParamTypeRector`](/rules/type-declaration/src/Rector/ClassMethod/AddMethodCallBasedParamTypeRector.php)
- [test fixtures](/rules/type-declaration/tests/Rector/ClassMethod/AddMethodCallBasedParamTypeRector/Fixture)

Change param type of passed `getId()` to UuidInterface type declaration

```diff
 class SomeClass
 {
-    public function getById($id)
+    public function getById(\Ramsey\Uuid\UuidInterface $id)
     {
     }
 }

 class CallerClass
 {
     public function run()
     {
         $building = new Building();
         $someClass = new SomeClass();
         $someClass->getById($building->getId());
     }
 }
```

<br><br>

### `AddParamTypeDeclarationRector`

- class: [`Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector`](/rules/type-declaration/src/Rector/ClassMethod/AddParamTypeDeclarationRector.php)
- [test fixtures](/rules/type-declaration/tests/Rector/ClassMethod/AddParamTypeDeclarationRector/Fixture)

Add param types where needed

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call(
            'configure',
            [[
                AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => inline_value_objects(
                                [new AddParamTypeDeclaration('SomeClass', 'process', 0, 'string')]
                            )
            ]]
        );
};
```

↓

```diff
 class SomeClass
 {
-    public function process($name)
+    public function process(string $name)
     {
     }
 }
```

<br><br>

### `CompleteVarDocTypePropertyRector`

- class: [`Rector\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector`](/rules/type-declaration/src/Rector/Property/CompleteVarDocTypePropertyRector.php)
- [test fixtures](/rules/type-declaration/tests/Rector/Property/CompleteVarDocTypePropertyRector/Fixture)

Complete property `@var` annotations or correct the old ones

```diff
 final class SomeClass
 {
+    /**
+     * @var EventDispatcher
+     */
     private $eventDispatcher;

     public function __construct(EventDispatcher $eventDispatcher)
     {
         $this->eventDispatcher = $eventDispatcher;
     }
 }
```

<br><br>

### `ParamTypeDeclarationRector`

- class: [`Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector`](/rules/type-declaration/src/Rector/FunctionLike/ParamTypeDeclarationRector.php)
- [test fixtures](/rules/type-declaration/tests/Rector/FunctionLike/ParamTypeDeclarationRector/Fixture)

Change @param types to type declarations if not a BC-break

```diff
 <?php

 class ParentClass
 {
     /**
      * @param int $number
      */
     public function keep($number)
     {
     }
 }

 final class ChildClass extends ParentClass
 {
     /**
      * @param int $number
      */
     public function keep($number)
     {
     }

     /**
      * @param int $number
      */
-    public function change($number)
+    public function change(int $number)
     {
     }
 }
```

<br><br>

### `PropertyTypeDeclarationRector`

- class: [`Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector`](/rules/type-declaration/src/Rector/Property/PropertyTypeDeclarationRector.php)
- [test fixtures](/rules/type-declaration/tests/Rector/Property/PropertyTypeDeclarationRector/Fixture)

Add @var to properties that are missing it

```diff
 class SomeClass
 {
+    /**
+     * @var int
+     */
     private $value;

     public function run()
     {
         $this->value = 123;
     }
 }
```

<br><br>

### `ReturnTypeDeclarationRector`

- class: [`Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector`](/rules/type-declaration/src/Rector/FunctionLike/ReturnTypeDeclarationRector.php)
- [test fixtures](/rules/type-declaration/tests/Rector/FunctionLike/ReturnTypeDeclarationRector/Fixture)

Change @return types and type from static analysis to type declarations if not a BC-break

```diff
 <?php

 class SomeClass
 {
-    /**
-     * @return int
-     */
-    public function getCount()
+    public function getCount(): int
     {
     }
 }
```

<br><br>

