# 669 Rules Overview

<br>

## Categories

- [Autodiscovery](#autodiscovery) (4)

- [CakePHP](#cakephp) (6)

- [Carbon](#carbon) (2)

- [CodeQuality](#codequality) (65)

- [CodeQualityStrict](#codequalitystrict) (4)

- [CodingStyle](#codingstyle) (38)

- [Composer](#composer) (5)

- [DeadCode](#deadcode) (49)

- [DeadDocBlock](#deaddocblock) (5)

- [Defluent](#defluent) (8)

- [DependencyInjection](#dependencyinjection) (3)

- [Doctrine](#doctrine) (18)

- [DoctrineCodeQuality](#doctrinecodequality) (11)

- [DoctrineGedmoToKnplabs](#doctrinegedmotoknplabs) (7)

- [DowngradePhp70](#downgradephp70) (2)

- [DowngradePhp71](#downgradephp71) (9)

- [DowngradePhp72](#downgradephp72) (3)

- [DowngradePhp73](#downgradephp73) (4)

- [DowngradePhp74](#downgradephp74) (11)

- [DowngradePhp80](#downgradephp80) (12)

- [EarlyReturn](#earlyreturn) (8)

- [Generic](#generic) (12)

- [Generics](#generics) (1)

- [Laravel](#laravel) (11)

- [Legacy](#legacy) (4)

- [MockeryToProphecy](#mockerytoprophecy) (2)

- [MockistaToMockery](#mockistatomockery) (2)

- [MysqlToMysqli](#mysqltomysqli) (4)

- [Naming](#naming) (11)

- [Nette](#nette) (20)

- [NetteCodeQuality](#nettecodequality) (8)

- [NetteKdyby](#nettekdyby) (4)

- [NetteTesterToPHPUnit](#nettetestertophpunit) (3)

- [NetteToSymfony](#nettetosymfony) (9)

- [NetteUtilsCodeQuality](#netteutilscodequality) (1)

- [Order](#order) (6)

- [PHPOffice](#phpoffice) (14)

- [PHPUnit](#phpunit) (38)

- [PHPUnitSymfony](#phpunitsymfony) (1)

- [PSR4](#psr4) (2)

- [Php52](#php52) (2)

- [Php53](#php53) (4)

- [Php54](#php54) (2)

- [Php55](#php55) (3)

- [Php56](#php56) (2)

- [Php70](#php70) (19)

- [Php71](#php71) (9)

- [Php72](#php72) (10)

- [Php73](#php73) (9)

- [Php74](#php74) (14)

- [Php80](#php80) (16)

- [PhpSpecToPHPUnit](#phpspectophpunit) (7)

- [Privatization](#privatization) (15)

- [RectorGenerator](#rectorgenerator) (1)

- [Removing](#removing) (6)

- [RemovingStatic](#removingstatic) (9)

- [Renaming](#renaming) (11)

- [Restoration](#restoration) (8)

- [Sensio](#sensio) (3)

- [Symfony](#symfony) (8)

- [Symfony2](#symfony2) (3)

- [Symfony3](#symfony3) (12)

- [Symfony4](#symfony4) (12)

- [Symfony5](#symfony5) (9)

- [SymfonyCodeQuality](#symfonycodequality) (2)

- [SymfonyPhpConfig](#symfonyphpconfig) (1)

- [Transform](#transform) (29)

- [TypeDeclaration](#typedeclaration) (13)

- [Visibility](#visibility) (3)

<br>

## Autodiscovery

### MoveEntitiesToEntityDirectoryRector

Move entities to Entity namespace

- class: `Rector\Autodiscovery\Rector\FileNode\MoveEntitiesToEntityDirectoryRector`

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

<br>

### MoveInterfacesToContractNamespaceDirectoryRector

Move interface to "Contract" namespace

- class: `Rector\Autodiscovery\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector`

```diff
-// file: app/Exception/Rule.php
+// file: app/Contract/Rule.php

-namespace App\Exception;
+namespace App\Contract;

 interface Rule
 {
 }
```

<br>

### MoveServicesBySuffixToDirectoryRector

Move classes by their suffix to their own group/directory

:wrench: **configure it!**

- class: `Rector\Autodiscovery\Rector\FileNode\MoveServicesBySuffixToDirectoryRector`

```php
use Rector\Autodiscovery\Rector\FileNode\MoveServicesBySuffixToDirectoryRector;
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

<br>

### MoveValueObjectsToValueObjectDirectoryRector

Move value object to ValueObject namespace/directory

:wrench: **configure it!**

- class: `Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector`

```php
use Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MoveValueObjectsToValueObjectDirectoryRector::class)
        ->call('configure', [[
            MoveValueObjectsToValueObjectDirectoryRector::TYPES => ['ValueObjectInterfaceClassName'],
            MoveValueObjectsToValueObjectDirectoryRector::SUFFIXES => ['Search'],
            MoveValueObjectsToValueObjectDirectoryRector::ENABLE_VALUE_OBJECT_GUESSING => true,
        ]]);
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

<br>

## CakePHP

### AppUsesStaticCallToUseStatementRector

Change `App::uses()` to use imports

- class: `Rector\CakePHP\Rector\Namespace_\AppUsesStaticCallToUseStatementRector`

```diff
-App::uses('NotificationListener', 'Event');
+use Event\NotificationListener;

 CakeEventManager::instance()->attach(new NotificationListener());
```

<br>

### ArrayToFluentCallRector

Moves array options to fluent setter method calls.

:wrench: **configure it!**

- class: `Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector`

```php
use Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector;
use Rector\CakePHP\ValueObject\ArrayToFluentCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayToFluentCallRector::class)
        ->call('configure', [[
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

### ChangeSnakedFixtureNameToPascalRector

Changes `$fixtues` style from snake_case to PascalCase.

- class: `Rector\CakePHP\Rector\Property\ChangeSnakedFixtureNameToPascalRector`

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

### ImplicitShortClassNameUseStatementRector

Collect implicit class names and add imports

- class: `Rector\CakePHP\Rector\FileWithoutNamespace\ImplicitShortClassNameUseStatementRector`

```diff
 use App\Foo\Plugin;
+use Cake\TestSuite\Fixture\TestFixture;

 class LocationsFixture extends TestFixture implements Plugin
 {
 }
```

<br>

### ModalToGetSetRector

Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.

:wrench: **configure it!**

- class: `Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector`

```php
use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
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

### RenameMethodCallBasedOnParameterRector

Changes method calls based on matching the first parameter value.

:wrench: **configure it!**

- class: `Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector`

```php
use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodCallBasedOnParameterRector::class)
        ->call('configure', [[
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

## Carbon

### ChangeCarbonSingularMethodCallToPluralRector

Change setter methods with args to their plural names on Carbon\Carbon

- class: `Rector\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector`

```diff
 use Carbon\Carbon;

 final class SomeClass
 {
     public function run(Carbon $carbon, $value): void
     {
-        $carbon->addMinute($value);
+        $carbon->addMinutes($value);
     }
 }
```

<br>

### ChangeDiffForHumansArgsRector

Change methods arguments of `diffForHumans()` on Carbon\Carbon

- class: `Rector\Carbon\Rector\MethodCall\ChangeDiffForHumansArgsRector`

```diff
 use Carbon\Carbon;

 final class SomeClass
 {
     public function run(Carbon $carbon): void
     {
-        $carbon->diffForHumans(null, true);
+        $carbon->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE);

-        $carbon->diffForHumans(null, false);
+        $carbon->diffForHumans(null, \Carbon\CarbonInterface::DIFF_RELATIVE_AUTO);
     }
 }
```

<br>

## CodeQuality

### AbsolutizeRequireAndIncludePathRector

include/require to absolute path. This Rector might introduce backwards incompatible code, when the include/require beeing changed depends on the current working directory.

- class: `Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector`

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

<br>

### AddPregQuoteDelimiterRector

Add `preg_quote` delimiter when missing

- class: `Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector`

```diff
-'#' . preg_quote('name') . '#';
+'#' . preg_quote('name', '#') . '#';
```

<br>

### AndAssignsToSeparateLinesRector

Split 2 assigns ands to separate line

- class: `Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector`

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

<br>

### ArrayKeyExistsTernaryThenValueToCoalescingRector

Change `array_key_exists()` ternary to coalesing

- class: `Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector`

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

<br>

### ArrayKeysAndInArrayToArrayKeyExistsRector

Replace `array_keys()` and `in_array()` to `array_key_exists()`

- class: `Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector`

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

<br>

### ArrayMergeOfNonArraysToSimpleArrayRector

Change `array_merge` of non arrays to array directly

- class: `Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector`

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

<br>

### ArrayThisCallToThisMethodCallRector

Change `[$this, someMethod]` without any args to `$this->someMethod()`

- class: `Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector`

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

<br>

### BooleanNotIdenticalToNotIdenticalRector

Negated identical boolean compare to not identical compare (does not apply to non-bool values)

- class: `Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector`

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

<br>

### CallableThisArrayToAnonymousFunctionRector

Convert [$this, "method"] to proper anonymous function

- class: `Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector`

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

<br>

### ChangeArrayPushToArrayAssignRector

Change `array_push()` to direct variable assign

- class: `Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector`

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

<br>

### CombineIfRector

Merges nested if statements

- class: `Rector\CodeQuality\Rector\If_\CombineIfRector`

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

<br>

### CombinedAssignRector

Simplify `$value` = `$value` + 5; assignments to shorter ones

- class: `Rector\CodeQuality\Rector\Assign\CombinedAssignRector`

```diff
-$value = $value + 5;
+$value += 5;
```

<br>

### CommonNotEqualRector

Use common != instead of less known <> with same meaning

- class: `Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector`

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

<br>

### CompactToVariablesRector

Change `compact()` call to own array

- class: `Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector`

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

<br>

### CompleteDynamicPropertiesRector

Add missing dynamic properties

- class: `Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector`

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

<br>

### ConsecutiveNullCompareReturnsToNullCoalesceQueueRector

Change multiple null compares to ?? queue

- class: `Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector`

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

<br>

### DateTimeToDateTimeInterfaceRector

Changes DateTime type-hint to DateTimeInterface

- class: `Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector`

```diff
 class SomeClass {
-    public function methodWithDateTime(\DateTime $dateTime)
+    /**
+     * @param \DateTime|\DateTimeImmutable $dateTime
+     */
+    public function methodWithDateTime(\DateTimeInterface $dateTime)
     {
         return true;
     }
 }
```

<br>

### ExplicitBoolCompareRector

Make if conditions more explicit

- class: `Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector`

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

<br>

### FixClassCaseSensitivityNameRector

Change miss-typed case sensitivity name to correct one

- class: `Rector\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector`

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

<br>

### FlipTypeControlToUseExclusiveTypeRector

Flip type control to use exclusive type

- class: `Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector`

```diff
 class SomeClass
 {
     public function __construct(array $values)
     {
-        /** @var PhpDocInfo|null $phpDocInfo */
         $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
-        if ($phpDocInfo === null) {
+        if (! $phpDocInfo instanceof PhpDocInfo) {
             return;
         }
     }
 }
```

<br>

### ForRepeatedCountToOwnVariableRector

Change `count()` in for function to own variable

- class: `Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector`

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

<br>

### ForToForeachRector

Change `for()` to `foreach()` where useful

- class: `Rector\CodeQuality\Rector\For_\ForToForeachRector`

```diff
 class SomeClass
 {
     public function run($tokens)
     {
-        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
-            if ($tokens[$i][0] === T_STRING && $tokens[$i][1] === 'fn') {
+        foreach ($tokens as $i => $token) {
+            if ($token[0] === T_STRING && $token[1] === 'fn') {
                 $tokens[$i][0] = self::T_FN;
             }
         }
     }
 }
```

<br>

### ForeachItemsAssignToEmptyArrayToAssignRector

Change `foreach()` items assign to empty array to direct assign

- class: `Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector`

```diff
 class SomeClass
 {
     public function run($items)
     {
         $collectedItems = [];

-        foreach ($items as $item) {
-             $collectedItems[] = $item;
-        }
+        $collectedItems = $items;
     }
 }
```

<br>

### ForeachToInArrayRector

Simplify `foreach` loops into `in_array` when possible

- class: `Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector`

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

<br>

### GetClassToInstanceOfRector

Changes comparison with `get_class` to instanceof

- class: `Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector`

```diff
-if (EventsListener::class === get_class($event->job)) { }
+if ($event->job instanceof EventsListener) { }
```

<br>

### InArrayAndArrayKeysToArrayKeyExistsRector

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

- class: `Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector`

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
```

<br>

### InlineIfToExplicitIfRector

Change inline if to explicit if

- class: `Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector`

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

<br>

### IntvalToTypeCastRector

Change `intval()` to faster and readable (int) `$value`

- class: `Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector`

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

<br>

### IsAWithStringWithThirdArgumentRector

Complete missing 3rd argument in case `is_a()` function in case of strings

- class: `Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector`

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

<br>

### IssetOnPropertyObjectToPropertyExistsRector

Change isset on property object to `property_exists()`

- class: `Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector`

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

<br>

### JoinStringConcatRector

Joins concat of 2 strings, unless the length is too long

- class: `Rector\CodeQuality\Rector\Concat\JoinStringConcatRector`

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

<br>

### LogicalToBooleanRector

Change OR, AND to ||, && with more common understanding

- class: `Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector`

```diff
-if ($f = false or true) {
+if (($f = false) || true) {
     return $f;
 }
```

<br>

### NewStaticToNewSelfRector

Change unsafe new `static()` to new `self()`

- class: `Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector`

```diff
 class SomeClass
 {
     public function build()
     {
-        return new static();
+        return new self();
     }
 }
```

<br>

### RemoveAlwaysTrueConditionSetInConstructorRector

If conditions is always true, perform the content right away

- class: `Rector\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector`

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

<br>

### RemoveSoleValueSprintfRector

Remove `sprintf()` wrapper if not needed

- class: `Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector`

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

<br>

### SetTypeToCastRector

Changes `settype()` to (type) where possible

- class: `Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector`

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

<br>

### ShortenElseIfRector

Shortens else/if to elseif

- class: `Rector\CodeQuality\Rector\If_\ShortenElseIfRector`

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

<br>

### SimplifyArraySearchRector

Simplify `array_search` to `in_array`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector`

```diff
-array_search("searching", $array) !== false;
+in_array("searching", $array);
```

<br>

```diff
-array_search("searching", $array, true) !== false;
+in_array("searching", $array, true);
```

<br>

### SimplifyBoolIdenticalTrueRector

Symplify bool value compare to true or false

- class: `Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector`

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

<br>

### SimplifyConditionsRector

Simplify conditions

- class: `Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector`

```diff
-if (! ($foo !== 'bar')) {...
+if ($foo === 'bar') {...
```

<br>

### SimplifyDeMorganBinaryRector

Simplify negated conditions with de Morgan theorem

- class: `Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector`

```diff
 $a = 5;
 $b = 10;
-$result = !($a > 20 || $b <= 50);
+$result = $a <= 20 && $b > 50;
```

<br>

### SimplifyDuplicatedTernaryRector

Remove ternary that duplicated return value of true : false

- class: `Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector`

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

<br>

### SimplifyEmptyArrayCheckRector

Simplify `is_array` and `empty` functions combination into a simple identical check for an empty array

- class: `Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector`

```diff
-is_array($values) && empty($values)
+$values === []
```

<br>

### SimplifyForeachToArrayFilterRector

Simplify foreach with function filtering to array filter

- class: `Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector`

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

<br>

### SimplifyForeachToCoalescingRector

Changes foreach that returns set value to ??

- class: `Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector`

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

<br>

### SimplifyFuncGetArgsCountRector

Simplify `count` of `func_get_args()` to `func_num_args()`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector`

```diff
-count(func_get_args());
+func_num_args();
```

<br>

### SimplifyIfElseToTernaryRector

Changes if/else for same value as assign to ternary

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector`

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

<br>

### SimplifyIfIssetToNullCoalescingRector

Simplify binary if to null coalesce

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector`

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

<br>

### SimplifyIfNotNullReturnRector

Changes redundant null check to instant return

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector`

```diff
 $newNode = 'something ;
-if ($newNode !== null) {
-    return $newNode;
-}
-
-return null;
+return $newNode;
```

<br>

### SimplifyIfReturnBoolRector

Shortens if return false/true to direct return

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector`

```diff
-if (strpos($docToken->getContent(), "\n") === false) {
-    return true;
-}
-
-return false;
+return strpos($docToken->getContent(), "\n") === false;
```

<br>

### SimplifyInArrayValuesRector

Removes unneeded `array_values()` in `in_array()` call

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector`

```diff
-in_array("key", array_values($array), true);
+in_array("key", $array, true);
```

<br>

### SimplifyRegexPatternRector

Simplify regex pattern to known ranges

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector`

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

<br>

### SimplifyStrposLowerRector

Simplify `strpos(strtolower()`, "...") calls

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector`

```diff
-strpos(strtolower($var), "...")"
+stripos($var, "...")"
```

<br>

### SimplifyTautologyTernaryRector

Simplify tautology ternary to value

- class: `Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector`

```diff
-$value = ($fullyQualifiedTypeHint !== $typeHint) ? $fullyQualifiedTypeHint : $typeHint;
+$value = $fullyQualifiedTypeHint;
```

<br>

### SimplifyUselessVariableRector

Removes useless variable assigns

- class: `Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector`

```diff
 function () {
-    $a = true;
-    return $a;
+    return true;
 };
```

<br>

### SingleInArrayToCompareRector

Changes `in_array()` with single element to ===

- class: `Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector`

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

<br>

### SingularSwitchToIfRector

Change switch with only 1 check to if

- class: `Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector`

```diff
 class SomeObject
 {
     public function run($value)
     {
         $result = 1;
-        switch ($value) {
-            case 100:
+        if ($value === 100) {
             $result = 1000;
         }

         return $result;
     }
 }
```

<br>

### SplitListAssignToSeparateLineRector

Splits `[$a, $b] = [5, 10]` scalar assign to standalone lines

- class: `Rector\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector`

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

<br>

### StrlenZeroToIdenticalEmptyStringRector

Changes `strlen` comparison to 0 to direct empty string compare

- class: `Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector`

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

<br>

### SwitchNegatedTernaryRector

Switch negated ternary condition rector

- class: `Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector`

```diff
 class SomeClass
 {
     public function run(bool $upper, string $name)
     {
-        return ! $upper
-            ? $name
-            : strtoupper($name);
+        return $upper
+            ? strtoupper($name)
+            : $name;
     }
 }
```

<br>

### ThrowWithPreviousExceptionRector

When throwing into a catch block, checks that the previous exception is passed to the new throw clause

- class: `Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector`

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

<br>

### UnnecessaryTernaryExpressionRector

Remove unnecessary ternary expressions.

- class: `Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector`

```diff
-$foo === $bar ? true : false;
+$foo === $bar;
```

<br>

### UnusedForeachValueToArrayKeysRector

Change foreach with unused `$value` but only `$key,` to `array_keys()`

- class: `Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector`

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

<br>

### UnwrapSprintfOneArgumentRector

unwrap `sprintf()` with one argument

- class: `Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector`

```diff
-echo sprintf('value');
+echo 'value';
```

<br>

### UseIdenticalOverEqualWithSameTypeRector

Use ===/!== over ==/!=, it values have the same type

- class: `Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector`

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

<br>

## CodeQualityStrict

### MoveOutMethodCallInsideIfConditionRector

Move out method call inside If condition

- class: `Rector\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector`

```diff
-if ($obj->run($arg) === 1) {
+$objRun = $obj->run($arg);
+if ($objRun === 1) {

 }
```

<br>

### MoveVariableDeclarationNearReferenceRector

Move variable declaration near its reference

- class: `Rector\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector`

```diff
-$var = 1;
 if ($condition === null) {
+    $var = 1;
     return $var;
 }
```

<br>

### ParamTypeToAssertTypeRector

Turn `@param` type to `assert` type

- class: `Rector\CodeQualityStrict\Rector\ClassMethod\ParamTypeToAssertTypeRector`

```diff
 class SomeClass
 {
     /**
      * @param \A|\B $arg
      */
     public function run($arg)
     {
-
+        \Webmozart\Assert\Assert::isAnyOf($arg, [\A::class, \B::class]);
     }
 }
```

<br>

### VarInlineAnnotationToAssertRector

Turn `@var` inline checks above code to `assert()` of the type

- class: `Rector\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector`

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

<br>

## CodingStyle

### AddArrayDefaultToArrayPropertyRector

Adds array default value to property to prevent foreach over null error

- class: `Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector`

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

<br>

### AddFalseDefaultToBoolPropertyRector

Add false default to bool properties, to prevent null compare errors

- class: `Rector\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector`

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

<br>

### BinarySwitchToIfElseRector

Changes switch with 2 options to if-else

- class: `Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector`

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

<br>

### CallUserFuncCallToVariadicRector

Replace call_user_func_call with variadic

- class: `Rector\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector`

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

<br>

### CamelCaseFunctionNamingToUnderscoreRector

Change CamelCase naming of functions to under_score naming

- class: `Rector\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector`

```diff
-function someCamelCaseFunction()
+function some_camel_case_function()
 {
 }

-someCamelCaseFunction();
+some_camel_case_function();
```

<br>

### CatchExceptionNameMatchingTypeRector

Type and name of catch exception should match

- class: `Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector`

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

<br>

### ConsistentImplodeRector

Changes various `implode` forms to consistent one

- class: `Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector`

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

<br>

### ConsistentPregDelimiterRector

Replace PREG delimiter with configured one

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector`

```php
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ConsistentPregDelimiterRector::class)
        ->call('configure', [[
            ConsistentPregDelimiterRector::DELIMITER => '#',
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

<br>

### CountArrayToEmptyArrayComparisonRector

Change `count` array comparison to empty array comparison to improve performance

- class: `Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector`

```diff
-count($array) === 0;
-count($array) > 0;
-! count($array);
+$array === [];
+$array !== [];
+$array === [];
```

<br>

### EncapsedStringsToSprintfRector

Convert enscaped {$string} to more readable `sprintf`

- class: `Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector`

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

<br>

### FollowRequireByDirRector

include/require should be followed by absolute path

- class: `Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector`

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

<br>

### FunctionCallToConstantRector

Changes use of function calls to use constants

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector`

```php
use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FunctionCallToConstantRector::class)
        ->call('configure', [[
            FunctionCallToConstantRector::FUNCTIONS_TO_CONSTANTS => [
                'php_sapi_name' => 'PHP_SAPI',
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
-        $value = php_sapi_name();
+        $value = PHP_SAPI;
     }
 }
```

<br>

```php
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

<br>

### MakeInheritedMethodVisibilitySameAsParentRector

Make method visibility same as parent one

- class: `Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector`

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

<br>

### ManualJsonStringToJsonEncodeArrayRector

Add extra space before new assign set

- class: `Rector\CodingStyle\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector`

```diff
+use Nette\Utils\Json;
+
 final class SomeClass
 {
     public function run()
     {
-        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"10"}}';
+        $data = [
+            'role_name' => 'admin',
+            'numberz' => ['id' => 10]
+        ];
+        $someJsonAsString = Json::encode($data);
     }
 }
```

<br>

### NewlineBeforeNewAssignSetRector

Add extra space before new assign set

- class: `Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector`

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

<br>

### NullableCompareToNullRector

Changes negate of empty comparison of nullable value to explicit === or !== compare

- class: `Rector\CodingStyle\Rector\If_\NullableCompareToNullRector`

```diff
 /** @var stdClass|null $value */
-if ($value) {
+if ($value !== null) {
 }

-if (!$value) {
+if ($value === null) {
 }
```

<br>

### PHPStormVarAnnotationRector

Change various `@var` annotation formats to one PHPStorm understands

- class: `Rector\CodingStyle\Rector\Assign\PHPStormVarAnnotationRector`

```diff
-$config = 5;
-/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+$config = 5;
```

<br>

### PostIncDecToPreIncDecRector

Use ++$value or --$value  instead of `$value++` or `$value--`

- class: `Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector`

```diff
 class SomeClass
 {
     public function run($value = 1)
     {
-        $value++; echo $value;
-        $value--; echo $value;
+        ++$value; echo $value;
+        --$value; echo $value;
     }
 }
```

<br>

### PreferThisOrSelfMethodCallRector

Changes `$this->...` and static:: to self:: or vise versa for given types

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector`

```php
use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PreferThisOrSelfMethodCallRector::class)
        ->call('configure', [[
            PreferThisOrSelfMethodCallRector::TYPE_TO_PREFERENCE => [
                TestCase::class => 'self',
            ],
        ]]);
};
```

↓

```diff
 class SomeClass extends \PHPUnit\Framework\TestCase
 {
     public function run()
     {
-        $this->assertEquals('a', 'a');
+        self::assertEquals('a', 'a');
     }
 }
```

<br>

### PreslashSimpleFunctionRector

Add pre-slash to short named functions to improve performance

- class: `Rector\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector`

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

<br>

### RemoveDoubleUnderscoreInMethodNameRector

Non-magic PHP object methods cannot start with "__"

- class: `Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector`

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

<br>

### RemoveUnusedAliasRector

Removes unused use aliases. Keep annotation aliases like "Doctrine\ORM\Mapping as ORM" to keep convention format

- class: `Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector`

```diff
-use Symfony\Kernel as BaseKernel;
+use Symfony\Kernel;

-class SomeClass extends BaseKernel
+class SomeClass extends Kernel
 {
 }
```

<br>

### ReturnArrayClassMethodToYieldRector

Turns array return to yield return in specific type and method

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector`

```php
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReturnArrayClassMethodToYieldRector::class)
        ->call('configure', [[
            ReturnArrayClassMethodToYieldRector::METHODS_TO_YIELDS => ValueObjectInliner::inline([
                new ReturnArrayClassMethodToYield('EventSubscriberInterface', 'getSubscribedEvents'),
            ]),
        ]]);
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

<br>

### SplitDoubleAssignRector

Split multiple inline assigns to each own lines default value, to prevent undefined array issues

- class: `Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector`

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

<br>

### SplitGroupedConstantsAndPropertiesRector

Separate constant and properties to own lines

- class: `Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector`

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

<br>

### SplitGroupedUseImportsRector

Split grouped use imports and trait statements to standalone lines

- class: `Rector\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector`

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

<br>

### SplitStringClassConstantToClassConstFetchRector

Separate class constant in a string to class constant fetch and string

- class: `Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector`

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

<br>

### StrictArraySearchRector

Makes `array_search` search for identical elements

- class: `Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector`

```diff
-array_search($value, $items);
+array_search($value, $items, true);
```

<br>

### SymplifyQuoteEscapeRector

Prefer quote that are not inside the string

- class: `Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector`

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

<br>

### TernaryConditionVariableAssignmentRector

Assign outcome of ternary condition to variable, where applicable

- class: `Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector`

```diff
 function ternary($value)
 {
-    $value ? $a = 1 : $a = 0;
+    $a = $value ? 1 : 0;
 }
```

<br>

### UnSpreadOperatorRector

Remove spread operator

- class: `Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector`

```diff
 class SomeClass
 {
-    public function run(...$array)
+    public function run(array $array)
     {
     }

     public function execute(array $data)
     {
-        $this->run(...$data);
+        $this->run($data);
     }
 }
```

<br>

### UseClassKeywordForClassNameResolutionRector

Use `class` keyword for class name resolution in string instead of hardcoded string reference

- class: `Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector`

```diff
-$value = 'App\SomeClass::someMethod()';
+$value = \App\SomeClass . '::someMethod()';
```

<br>

### UseIncrementAssignRector

Use ++ increment instead of `$var += 1`

- class: `Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector`

```diff
 class SomeClass
 {
     public function run()
     {
-        $style += 1;
+        ++$style;
     }
 }
```

<br>

### UseMessageVariableForSprintfInSymfonyStyleRector

Decouple `$message` property from `sprintf()` calls in `$this->symfonyStyle->method()`

- class: `Rector\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector`

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

<br>

### VarConstantCommentRector

`Constant` should have a `@var` comment with type

- class: `Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector`

```diff
 class SomeClass
 {
+    /**
+     * @var string
+     */
     const HI = 'hi';
 }
```

<br>

### VersionCompareFuncCallToConstantRector

Changes use of call to version compare function to use of PHP version constant

- class: `Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector`

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

<br>

### WrapEncapsedVariableInCurlyBracesRector

Wrap encapsed variables in curly braces

- class: `Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector`

```diff
 function run($world)
 {
-    echo "Hello $world!"
+    echo "Hello {$world}!"
 }
```

<br>

### YieldClassMethodToArrayClassMethodRector

Turns yield return to array return in specific type and method

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector`

```php
use Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(YieldClassMethodToArrayClassMethodRector::class)
        ->call('configure', [[
            YieldClassMethodToArrayClassMethodRector::METHODS_BY_TYPE => [
                'EventSubscriberInterface' => ['getSubscribedEvents'],
            ],
        ]]);
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

<br>

## Composer

### AddPackageToRequireComposerRector

Add package to "require" in `composer.json`

:wrench: **configure it!**

- class: `Rector\Composer\Rector\AddPackageToRequireComposerRector`

```php
use Rector\Composer\Rector\AddPackageToRequireComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddPackageToRequireComposerRector::class)
        ->call('configure', [[
            AddPackageToRequireComposerRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('symfony/console', '^3.4'),
            ]),
        ]]);
};
```

↓

```diff
 {
+    "require": {
+        "symfony/console": "^3.4"
+    }
 }
```

<br>

### AddPackageToRequireDevComposerRector

Add package to "require-dev" in `composer.json`

:wrench: **configure it!**

- class: `Rector\Composer\Rector\AddPackageToRequireDevComposerRector`

```php
use Rector\Composer\Rector\AddPackageToRequireDevComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddPackageToRequireDevComposerRector::class)
        ->call('configure', [[
            AddPackageToRequireDevComposerRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('symfony/console', '^3.4'),
            ]),
        ]]);
};
```

↓

```diff
 {
+    "require-dev": {
+        "symfony/console": "^3.4"
+    }
 }
```

<br>

### ChangePackageVersionComposerRector

Change package version `composer.json`

:wrench: **configure it!**

- class: `Rector\Composer\Rector\ChangePackageVersionComposerRector`

```php
use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangePackageVersionComposerRector::class)
        ->call('configure', [[
            ChangePackageVersionComposerRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('symfony/console', '^4.4'),
            ]),
        ]]);
};
```

↓

```diff
 {
-    "require-dev": {
-        "symfony/console": "^3.4"
+    "require": {
+        "symfony/console": "^4.4"
     }
 }
```

<br>

### RemovePackageComposerRector

Remove package from "require" and "require-dev" in `composer.json`

:wrench: **configure it!**

- class: `Rector\Composer\Rector\RemovePackageComposerRector`

```php
use Rector\Composer\Rector\RemovePackageComposerRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemovePackageComposerRector::class)
        ->call('configure', [[
            RemovePackageComposerRector::PACKAGE_NAMES => ['symfony/console'],
        ]]);
};
```

↓

```diff
 {
-    "require": {
-        "symfony/console": "^3.4"
-    }
 }
```

<br>

### ReplacePackageAndVersionComposerRector

Change package name and version `composer.json`

:wrench: **configure it!**

- class: `Rector\Composer\Rector\ReplacePackageAndVersionComposerRector`

```php
use Rector\Composer\Rector\ReplacePackageAndVersionComposerRector;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplacePackageAndVersionComposerRector::class)
        ->call('configure', [[
            ReplacePackageAndVersionComposerRector::REPLACE_PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new ReplacePackageAndVersion('symfony/console', 'symfony/http-kernel', '^4.4'),
            ]),
        ]]);
};
```

↓

```diff
 {
     "require-dev": {
-        "symfony/console": "^3.4"
+        "symfony/http-kernel": "^4.4"
     }
 }
```

<br>

## DeadCode

### RecastingRemovalRector

Removes recasting of the same type

- class: `Rector\DeadCode\Rector\Cast\RecastingRemovalRector`

```diff
 $string = '';
-$string = (string) $string;
+$string = $string;

 $array = [];
-$array = (array) $array;
+$array = $array;
```

<br>

### RemoveAlwaysTrueIfConditionRector

Remove if condition that is always true

- class: `Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector`

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

<br>

### RemoveAndTrueRector

Remove and true that has no added value

- class: `Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector`

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

<br>

### RemoveAssignOfVoidReturnFunctionRector

Remove assign of void function/method to variable

- class: `Rector\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector`

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

<br>

### RemoveCodeAfterReturnRector

Remove dead code after return statement

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector`

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

<br>

### RemoveConcatAutocastRector

Remove (string) casting when it comes to concat, that does this by default

- class: `Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector`

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

<br>

### RemoveDeadConditionAboveReturnRector

Remove dead condition above return

- class: `Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector`

```diff
 final class SomeClass
 {
     public function go()
     {
-        if (1 === 1) {
-            return 'yes';
-        }
-
         return 'yes';
     }
 }
```

<br>

### RemoveDeadConstructorRector

Remove empty constructor

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector`

```diff
 class SomeClass
 {
-    public function __construct()
-    {
-    }
 }
```

<br>

### RemoveDeadIfForeachForRector

Remove if, foreach and for that does not do anything

- class: `Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector`

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

<br>

### RemoveDeadInstanceOfRector

Remove dead instanceof check on type hinted variable

- class: `Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector`

```diff
 final class SomeClass
 {
     public function go(stdClass $stdClass)
     {
-        if (! $stdClass instanceof stdClass) {
-            return false;
-        }
-
         return true;
     }
 }
```

<br>

### RemoveDeadRecursiveClassMethodRector

Remove unused public method that only calls itself recursively

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector`

```diff
 class SomeClass
 {
-    public function run()
-    {
-        return $this->run();
-    }
 }
```

<br>

### RemoveDeadReturnRector

Remove last return in the functions, since does not do anything

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector`

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

<br>

### RemoveDeadStmtRector

Removes dead code statements

- class: `Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector`

```diff
-$value = 5;
-$value;
+$value = 5;
```

<br>

### RemoveDeadTryCatchRector

Remove dead try/catch

- class: `Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector`

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

<br>

### RemoveDeadZeroAndOneOperationRector

Remove operation with 1 and 0, that have no effect on the value

- class: `Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector`

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

<br>

### RemoveDefaultArgumentValueRector

Remove argument value, if it is the same as default value

- class: `Rector\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector`

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

<br>

### RemoveDelegatingParentCallRector

Removed dead parent call, that does not change anything

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector`

```diff
 class SomeClass
 {
-    public function prettyPrint(array $stmts): string
-    {
-        return parent::prettyPrint($stmts);
-    }
 }
```

<br>

### RemoveDoubleAssignRector

Simplify useless double assigns

- class: `Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector`

```diff
-$value = 1;
 $value = 1;
```

<br>

### RemoveDuplicatedArrayKeyRector

Remove duplicated `key` in defined arrays.

- class: `Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector`

```diff
 $item = [
-    1 => 'A',
     1 => 'B'
 ];
```

<br>

### RemoveDuplicatedCaseInSwitchRector

2 following switch keys with identical  will be reduced to one result

- class: `Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector`

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

<br>

### RemoveDuplicatedIfReturnRector

Remove duplicated if stmt with return in function/method body

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector`

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

<br>

### RemoveDuplicatedInstanceOfRector

Remove duplicated instanceof in one call

- class: `Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector`

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

<br>

### RemoveEmptyClassMethodRector

Remove empty method calls not required by parents

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector`

```diff
 class OrphanClass
 {
-    public function __construct()
-    {
-    }
 }
```

<br>

### RemoveEmptyMethodCallRector

Remove empty method call

- class: `Rector\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector`

```diff
 class SomeClass
 {
     public function callThis()
     {
     }
 }

-$some = new SomeClass();
-$some->callThis();
+$some = new SomeClass();
```

<br>

### RemoveNullPropertyInitializationRector

Remove initialization with null value from property declarations

- class: `Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector`

```diff
 class SunshineCommand extends ParentClassWithNewConstructor
 {
-    private $myVar = null;
+    private $myVar;
 }
```

<br>

### RemoveOverriddenValuesRector

Remove initial assigns of overridden values

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector`

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

<br>

### RemoveParentCallWithoutParentRector

Remove unused parent call with no parent class

- class: `Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector`

```diff
 class OrphanClass
 {
     public function __construct()
     {
-         parent::__construct();
     }
 }
```

<br>

### RemoveSetterOnlyPropertyAndMethodCallRector

Removes method that set values that are never used

- class: `Rector\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector`

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

<br>

### RemoveUnreachableStatementRector

Remove unreachable statements

- class: `Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector`

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

<br>

### RemoveUnusedAssignVariableRector

Remove assigned unused variable

- class: `Rector\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector`

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

<br>

### RemoveUnusedClassConstantRector

Remove unused class constants

- class: `Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector`

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

<br>

### RemoveUnusedClassesRector

Remove unused classes without interface

- class: `Rector\DeadCode\Rector\Class_\RemoveUnusedClassesRector`

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

<br>

### RemoveUnusedConstructorParamRector

Remove unused parameter in constructor

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector`

```diff
 final class SomeClass
 {
     private $hey;

-    public function __construct($hey, $man)
+    public function __construct($hey)
     {
         $this->hey = $hey;
     }
 }
```

<br>

### RemoveUnusedDoctrineEntityMethodAndPropertyRector

Removes unused methods and properties from Doctrine entity classes

- class: `Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector`

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

<br>

### RemoveUnusedForeachKeyRector

Remove unused `key` in foreach

- class: `Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector`

```diff
 $items = [];
-foreach ($items as $key => $value) {
+foreach ($items as $value) {
     $result = $value;
 }
```

<br>

### RemoveUnusedFunctionRector

Remove unused function

- class: `Rector\DeadCode\Rector\Function_\RemoveUnusedFunctionRector`

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

<br>

### RemoveUnusedNonEmptyArrayBeforeForeachRector

Remove unused if check to non-empty array before foreach of the array

- class: `Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector`

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

<br>

### RemoveUnusedParameterRector

Remove unused parameter, if not required by interface or parent class

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector`

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

<br>

### RemoveUnusedPrivateConstantRector

Remove unused private constant

- class: `Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector`

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

<br>

### RemoveUnusedPrivateMethodRector

Remove unused private method

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector`

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

<br>

### RemoveUnusedPrivatePropertyRector

Remove unused private properties

- class: `Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector`

```diff
 class SomeClass
 {
-    private $property;
 }
```

<br>

### RemoveUnusedPublicMethodRector

Remove unused public method

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodRector`

```diff
 class SomeClass
 {
-    public function unusedpublicMethod()
-    {
-        // ...
-    }
-
     public function execute()
     {
         // ...
     }

     public function run()
     {
         $obj = new self;
         $obj->execute();
     }
 }
```

<br>

### RemoveUnusedVariableAssignRector

Remove unused assigns to variables

- class: `Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector`

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = 5;
     }
 }
```

<br>

### RemoveUselessJustForSakeInterfaceRector

Remove interface, that are added just for its sake, but nowhere useful

- class: `Rector\DeadCode\Rector\Class_\RemoveUselessJustForSakeInterfaceRector`

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

<br>

### SimplifyIfElseWithSameContentRector

Remove if/else if they have same content

- class: `Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector`

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

<br>

### SimplifyMirrorAssignRector

Removes unneeded $a = $a assigns

- class: `Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector`

```diff
-$a = $a;
```

<br>

### TernaryToBooleanOrFalseToBooleanAndRector

Change ternary of bool : false to && bool

- class: `Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector`

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

<br>

### UnwrapFutureCompatibleIfFunctionExistsRector

Remove functions exists if with else for always existing

- class: `Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector`

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

<br>

### UnwrapFutureCompatibleIfPhpVersionRector

Remove php version checks if they are passed

- class: `Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector`

```diff
 // current PHP: 7.2
-if (version_compare(PHP_VERSION, '7.2', '<')) {
-    return 'is PHP 7.1-';
-} else {
-    return 'is PHP 7.2+';
-}
+return 'is PHP 7.2+';
```

<br>

## DeadDocBlock

### RemoveAnnotationRector

Remove annotation by names

:wrench: **configure it!**

- class: `Rector\DeadDocBlock\Rector\ClassLike\RemoveAnnotationRector`

```php
use Rector\DeadDocBlock\Rector\ClassLike\RemoveAnnotationRector;
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

<br>

### RemoveNonExistingVarAnnotationRector

Removes non-existing `@var` annotations above the code

- class: `Rector\DeadDocBlock\Rector\Node\RemoveNonExistingVarAnnotationRector`

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

<br>

### RemoveUselessParamTagRector

Remove `@param` docblock with same type as parameter type

- class: `Rector\DeadDocBlock\Rector\ClassMethod\RemoveUselessParamTagRector`

```diff
 class SomeClass
 {
     /**
-     * @param string $a
      * @param string $b description
      */
     public function foo(string $a, string $b)
     {
     }
 }
```

<br>

### RemoveUselessReturnTagRector

Remove `@return` docblock with same type as defined in PHP

- class: `Rector\DeadDocBlock\Rector\ClassMethod\RemoveUselessReturnTagRector`

```diff
 use stdClass;

 class SomeClass
 {
-    /**
-     * @return stdClass
-     */
     public function foo(): stdClass
     {
     }
 }
```

<br>

### RemoveUselessVarTagRector

Remove unused `@var` annotation for properties

- class: `Rector\DeadDocBlock\Rector\Property\RemoveUselessVarTagRector`

```diff
 final class SomeClass
 {
-    /**
-     * @var string
-     */
     public string $name = 'name';
 }
```

<br>

## Defluent

### DefluentReturnMethodCallRector

Turns return of fluent, to standalone call line and return of value

- class: `Rector\Defluent\Rector\Return_\DefluentReturnMethodCallRector`

```diff
 $someClass = new SomeClass();
-return $someClass->someFunction();
+$someClass->someFunction();
+return $someClass;
```

<br>

### FluentChainMethodCallToNormalMethodCallRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector`

```diff
 $someClass = new SomeClass();
-$someClass->someFunction()
-            ->otherFunction();
+$someClass->someFunction();
+$someClass->otherFunction();
```

<br>

### InArgFluentChainMethodCallToStandaloneMethodCallRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector`

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

<br>

### MethodCallOnSetterMethodCallToStandaloneAssignRector

Change method call on setter to standalone assign before the setter

- class: `Rector\Defluent\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector`

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

<br>

### NewFluentChainMethodCallToNonFluentRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector`

```diff
-(new SomeClass())->someFunction()
-            ->otherFunction();
+$someClass = new SomeClass();
+$someClass->someFunction();
+$someClass->otherFunction();
```

<br>

### ReturnFluentChainMethodCallToNormalMethodCallRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector`

```diff
 $someClass = new SomeClass();
-return $someClass->someFunction()
-            ->otherFunction();
+$someClass->someFunction();
+$someClass->otherFunction();
+return $someClass;
```

<br>

### ReturnNewFluentChainMethodCallToNonFluentRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector`

```diff
-return (new SomeClass())->someFunction()
-            ->otherFunction();
+$someClass = new SomeClass();
+$someClass->someFunction();
+$someClass->otherFunction();
+return $someClass;
```

<br>

### ReturnThisRemoveRector

Removes "return `$this;"` from *fluent interfaces* for specified classes.

- class: `Rector\Defluent\Rector\ClassMethod\ReturnThisRemoveRector`

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

<br>

## DependencyInjection

### ActionInjectionToConstructorInjectionRector

Turns action injection in Controllers to constructor injection

- class: `Rector\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector`

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

<br>

### MultiParentingToAbstractDependencyRector

Move dependency passed to all children to parent as `@inject/@required` dependency

:wrench: **configure it!**

- class: `Rector\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector`

```php
use Rector\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MultiParentingToAbstractDependencyRector::class)
        ->call('configure', [[
            MultiParentingToAbstractDependencyRector::FRAMEWORK => 'nette',
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

<br>

### ReplaceVariableByPropertyFetchRector

Turns variable in controller action to property fetch, as follow up to action injection variable to property change.

- class: `Rector\DependencyInjection\Rector\Variable\ReplaceVariableByPropertyFetchRector`

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

<br>

## Doctrine

### AddEntityIdByConditionRector

Add entity id with annotations when meets condition

:wrench: **configure it!**

- class: `Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector`

```php
use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddEntityIdByConditionRector::class)
        ->call('configure', [[
            AddEntityIdByConditionRector::DETECTED_TRAITS => [
                'Knp\DoctrineBehaviors\Model\Translatable\Translation',
                'Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait',
            ],
        ]]);
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

<br>

### AddUuidAnnotationsToIdPropertyRector

Add uuid annotations to `$id` property

- class: `Rector\Doctrine\Rector\Property\AddUuidAnnotationsToIdPropertyRector`

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

<br>

### AddUuidMirrorForRelationPropertyRector

Adds `$uuid` property to entities, that already have `$id` with integer type.Require for step-by-step migration from int to uuid.

- class: `Rector\Doctrine\Rector\Class_\AddUuidMirrorForRelationPropertyRector`

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

<br>

### AddUuidToEntityWhereMissingRector

Adds `$uuid` property to entities, that already have `$id` with integer type.Require for step-by-step migration from int to uuid. In following step it should be renamed to `$id` and replace it

- class: `Rector\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector`

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

<br>

### AlwaysInitializeUuidInEntityRector

Add uuid initializion to all entities that misses it

- class: `Rector\Doctrine\Rector\Class_\AlwaysInitializeUuidInEntityRector`

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

<br>

### ChangeGetIdTypeToUuidRector

Change return type of `getId()` to uuid interface

- class: `Rector\Doctrine\Rector\ClassMethod\ChangeGetIdTypeToUuidRector`

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

<br>

### ChangeGetUuidMethodCallToGetIdRector

Change `getUuid()` method call to `getId()`

- class: `Rector\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector`

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

<br>

### ChangeIdenticalUuidToEqualsMethodCallRector

Change `$uuid` === 1 to `$uuid->equals(\Ramsey\Uuid\Uuid::fromString(1))`

- class: `Rector\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector`

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

<br>

### ChangeReturnTypeOfClassMethodWithGetIdRector

Change `getUuid()` method call to `getId()`

- class: `Rector\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector`

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

<br>

### ChangeSetIdToUuidValueRector

Change set id to uuid values

- class: `Rector\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector`

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

<br>

### ChangeSetIdTypeToUuidRector

Change param type of `setId()` to uuid interface

- class: `Rector\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector`

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

<br>

### EntityAliasToClassConstantReferenceRector

Replaces doctrine alias with class.

:wrench: **configure it!**

- class: `Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector`

```php
use Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(EntityAliasToClassConstantReferenceRector::class)
        ->call('configure', [[
            EntityAliasToClassConstantReferenceRector::ALIASES_TO_NAMESPACES => [
                App::class => 'App\Entity',
            ],
        ]]);
};
```

↓

```diff
 $entityManager = new Doctrine\ORM\EntityManager();
-$entityManager->getRepository("AppBundle:Post");
+$entityManager->getRepository(\App\Entity\Post::class);
```

<br>

### ManagerRegistryGetManagerToEntityManagerRector

Changes ManagerRegistry intermediate calls directly to EntityManager calls

- class: `Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector`

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

<br>

### RemoveRepositoryFromEntityAnnotationRector

Removes repository class from `@Entity` annotation

- class: `Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector`

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

<br>

### RemoveTemporaryUuidColumnPropertyRector

Remove temporary `$uuid` property

- class: `Rector\Doctrine\Rector\Property\RemoveTemporaryUuidColumnPropertyRector`

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

<br>

### RemoveTemporaryUuidRelationPropertyRector

Remove temporary *Uuid relation properties

- class: `Rector\Doctrine\Rector\Property\RemoveTemporaryUuidRelationPropertyRector`

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

<br>

### ReplaceParentRepositoryCallsByRepositoryPropertyRector

Handles method calls in child of Doctrine EntityRepository and moves them to `$this->repository` property.

- class: `Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector`

```diff
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

<br>

### ServiceEntityRepositoryParentCallToDIRector

Change ServiceEntityRepository to dependency injection, with repository property

- class: `Rector\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector`

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

<br>

## DoctrineCodeQuality

### ChangeBigIntEntityPropertyToIntTypeRector

Change database type "bigint" for @var/type declaration to string

- class: `Rector\DoctrineCodeQuality\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector`

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

<br>

### ChangeSetParametersArrayToArrayCollectionRector

Change array to ArrayCollection in setParameters method of query builder

- class: `Rector\DoctrineCodeQuality\Rector\MethodCall\ChangeSetParametersArrayToArrayCollectionRector`

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

<br>

### CorrectDefaultTypesOnEntityPropertyRector

Change default value types to match Doctrine annotation type

- class: `Rector\DoctrineCodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector`

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

<br>

### ImproveDoctrineCollectionDocTypeInEntityRector

Improve @var, `@param` and `@return` types for Doctrine collections to make them useful both for PHPStan and PHPStorm

- class: `Rector\DoctrineCodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector`

```diff
 use Doctrine\Common\Collections\Collection;
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SomeClass
 {
     /**
      * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
-     * @var Collection|Trainer[]
+     * @var Collection<int, Training>|Trainer[]
      */
     private $trainings = [];
 }
```

<br>

### InitializeDefaultEntityCollectionRector

Initialize collection property in Entity constructor

- class: `Rector\DoctrineCodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector`

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

<br>

### MakeEntityDateTimePropertyDateTimeInterfaceRector

Make maker bundle generate DateTime property accept DateTimeInterface too

- class: `Rector\DoctrineCodeQuality\Rector\ClassMethod\MakeEntityDateTimePropertyDateTimeInterfaceRector`

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

<br>

### MakeEntitySetterNullabilityInSyncWithPropertyRector

Make nullability in setter class method with respect to property

- class: `Rector\DoctrineCodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector`

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

<br>

### MoveCurrentDateTimeDefaultInEntityToConstructorRector

Move default value for entity property to constructor, the safest place

- class: `Rector\DoctrineCodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector`

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

<br>

### MoveRepositoryFromParentToConstructorRector

Turns parent EntityRepository class to constructor dependency

- class: `Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector`

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

<br>

### RemoveRedundantDefaultClassAnnotationValuesRector

Removes redundant default values from Doctrine ORM annotations on class level

- class: `Rector\DoctrineCodeQuality\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector`

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
- * @ORM\Entity(readOnly=false)
+ * @ORM\Entity()
  */
 class SomeClass
 {
 }
```

<br>

### RemoveRedundantDefaultPropertyAnnotationValuesRector

Removes redundant default values from Doctrine ORM annotations on class property level

- class: `Rector\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector`

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SomeClass
 {
     /**
      * @ORM\ManyToOne(targetEntity=Training::class)
-     * @ORM\JoinColumn(name="training", unique=false)
+     * @ORM\JoinColumn(name="training")
      */
     private $training;
 }
```

<br>

## DoctrineGedmoToKnplabs

### BlameableBehaviorRector

Change Blameable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\BlameableBehaviorRector`

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

<br>

### LoggableBehaviorRector

Change Loggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\LoggableBehaviorRector`

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

<br>

### SluggableBehaviorRector

Change Sluggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\SluggableBehaviorRector`

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

<br>

### SoftDeletableBehaviorRector

Change SoftDeletable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\SoftDeletableBehaviorRector`

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

<br>

### TimestampableBehaviorRector

Change Timestampable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\TimestampableBehaviorRector`

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

<br>

### TranslationBehaviorRector

Change Translation from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\TranslationBehaviorRector`

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

<br>

### TreeBehaviorRector

Change Tree from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\TreeBehaviorRector`

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

<br>

## DowngradePhp70

### DowngradeTypeParamDeclarationRector

Remove the type params, add `@param` tags instead

- class: `Rector\DowngradePhp70\Rector\FunctionLike\DowngradeTypeParamDeclarationRector`

```diff
 class SomeClass
 {
-    public function run(string $input)
+    /**
+     * @param string $input
+     */
+    public function run($input)
     {
         // do something
     }
 }
```

<br>

### DowngradeTypeReturnDeclarationRector

Remove returning types, add a `@return` tag instead

- class: `Rector\DowngradePhp70\Rector\FunctionLike\DowngradeTypeReturnDeclarationRector`

```diff
 class SomeClass
 {
-    public function getResponse(): string
+    /**
+     * @return string
+     */
+    public function getResponse()
     {
         return 'Hello world';
     }
 }
```

<br>

## DowngradePhp71

### DowngradeClassConstantVisibilityRector

Downgrade class constant visibility

- class: `Rector\DowngradePhp71\Rector\ClassConst\DowngradeClassConstantVisibilityRector`

```diff
 class SomeClass
 {
-   public const PUBLIC_CONST_B = 2;
-   protected const PROTECTED_CONST = 3;
-   private const PRIVATE_CONST = 4;
+   const PUBLIC_CONST_B = 2;
+   const PROTECTED_CONST = 3;
+   const PRIVATE_CONST = 4;
 }
```

<br>

### DowngradeIterablePseudoTypeParamDeclarationRector

Remove the iterable pseudo type params, add `@param` tags instead

- class: `Rector\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeParamDeclarationRector`

```diff
 class SomeClass
 {
-    public function run(iterable $iterator)
+    /**
+     * @param mixed[]|\Traversable $iterator
+     */
+    public function run($iterator)
     {
         // do something
     }
 }
```

<br>

### DowngradeIterablePseudoTypeReturnDeclarationRector

Remove returning iterable pseud type, add a `@return` tag instead

- class: `Rector\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeReturnDeclarationRector`

```diff
 class SomeClass
 {
-    public function run(): iterable
+    /**
+     * @return mixed[]|\Traversable
+     */
+    public function run()
     {
         // do something
     }
 }
```

<br>

### DowngradeNegativeStringOffsetToStrlenRector

Downgrade negative string offset to `strlen`

- class: `Rector\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector`

```diff
-echo 'abcdef'[-2];
-echo strpos('aabbcc', 'b', -3);
-echo strpos($var, 'b', -3);
+echo 'abcdef'[strlen('abcdef') - 2];
+echo strpos('aabbcc', 'b', strlen('aabbcc') - 3);
+echo strpos($var, 'b', strlen($var) - 3);
```

<br>

### DowngradeNullableTypeParamDeclarationRector

Remove the nullable type params, add `@param` tags instead

- class: `Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector`

```diff
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

<br>

### DowngradeNullableTypeReturnDeclarationRector

Remove returning nullable types, add a `@return` tag instead

- class: `Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeReturnDeclarationRector`

```diff
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

<br>

### DowngradePipeToMultiCatchExceptionRector

Downgrade single one | separated to multi catch exception

- class: `Rector\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector`

```diff
 try {
     // Some code...
- } catch (ExceptionType1 | ExceptionType2 $exception) {
+} catch (ExceptionType1 $exception) {
     $sameCode;
- }
+} catch (ExceptionType2 $exception) {
+    $sameCode;
+}
```

<br>

### DowngradeVoidTypeReturnDeclarationRector

Remove "void" return type, add a `"@return` void" tag instead

- class: `Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector`

```diff
 class SomeClass
 {
-    public function run(): void
+    /**
+     * @return void
+     */
+    public function run()
     {
     }
 }
```

<br>

### SymmetricArrayDestructuringToListRector

Downgrade Symmetric array destructuring to `list()` function

- class: `Rector\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector`

```diff
-[$id1, $name1] = $data;
+list($id1, $name1) = $data;
```

<br>

## DowngradePhp72

### DowngradeParamObjectTypeDeclarationRector

Remove the 'PHPStan\Type\ObjectWithoutClassType' param type, add a `@param` tag instead

- class: `Rector\DowngradePhp72\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector`

```diff
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

<br>

### DowngradeParameterTypeWideningRector

Remove argument type declarations in the parent and in all child classes, whenever some child class removes it

- class: `Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector`

```diff
 interface A
 {
-    public function test(array $input);
+    /**
+     * @param array $input
+     */
+    public function test($input);
 }

 class B implements A
 {
     public function test($input){} // type omitted for $input
 }

 class C implements A
 {
-    public function test(array $input){}
+    /**
+     * @param array $input
+     */
+    public function test($input);
 }
```

<br>

### DowngradeReturnObjectTypeDeclarationRector

Remove "object" return type, add a `"@return` object" tag instead

- class: `Rector\DowngradePhp72\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector`

```diff
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

<br>

## DowngradePhp73

### DowngradeFlexibleHeredocSyntaxRector

Changes heredoc/nowdoc that contains closing word to safe wrapper name

- class: `Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector`

```diff
 $query = <<<SQL
-    SELECT *
-    FROM `table`
-    WHERE `column` = true;
-    SQL;
+SELECT *
+FROM `table`
+WHERE `column` = true;
+SQL;
```

<br>

### DowngradeListReferenceAssignmentRector

Convert the list reference assignment to its equivalent PHP 7.2 code

- class: `Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector`

```diff
 class SomeClass
 {
     public function run($string)
     {
-        $array = [1, 2, 3];
-        list($a, &$b) = $array;
+        $array = [1, 2];
+        list($a) = $array;
+        $b =& $array[1];

-        [&$c, $d, &$e] = $array;
+        [$c, $d, $e] = $array;
+        $c =& $array[0];
+        $e =& $array[2];

-        list(&$a, &$b) = $array;
+        $a =& $array[0];
+        $b =& $array[1];
     }
 }
```

<br>

### DowngradeTrailingCommasInFunctionCallsRector

Remove trailing commas in function calls

- class: `Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector`

```diff
 class SomeClass
 {
     public function __construct(string $value)
     {
         $compacted = compact(
             'posts',
-            'units',
+            'units'
         );
     }
 }
```

<br>

### SetCookieOptionsArrayToArgumentsRector

Convert `setcookie` option array to arguments

- class: `Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector`

```diff
-setcookie('name', $value, ['expires' => 360]);
+setcookie('name', $value, 360);
```

<br>

## DowngradePhp74

### ArrowFunctionToAnonymousFunctionRector

Replace arrow functions with anonymous functions

- class: `Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector`

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

<br>

### DowngradeArrayMergeCallWithoutArgumentsRector

Add missing param to `array_merge` and `array_merge_recursive`

- class: `Rector\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector`

```diff
 class SomeClass
 {
     public function run()
     {
-        array_merge();
-        array_merge_recursive();
+        array_merge([]);
+        array_merge_recursive([]);
     }
 }
```

<br>

### DowngradeArraySpreadRector

Replace array spread with `array_merge` function

- class: `Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector`

```diff
 class SomeClass
 {
     public function run()
     {
         $parts = ['apple', 'pear'];
-        $fruits = ['banana', 'orange', ...$parts, 'watermelon'];
+        $fruits = array_merge(['banana', 'orange'], $parts, ['watermelon']);
     }

     public function runWithIterable()
     {
-        $fruits = ['banana', 'orange', ...new ArrayIterator(['durian', 'kiwi']), 'watermelon'];
+        $item0Unpacked = new ArrayIterator(['durian', 'kiwi']);
+        $fruits = array_merge(['banana', 'orange'], is_array($item0Unpacked) ? $item0Unpacked : iterator_to_array($item0Unpacked), ['watermelon']);
     }
 }
```

<br>

### DowngradeContravariantArgumentTypeRector

Remove contravariant argument type declarations

- class: `Rector\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector`

```diff
 class ParentType {}
 class ChildType extends ParentType {}

 class A
 {
     public function contraVariantArguments(ChildType $type)
     { /* … */ }
 }

 class B extends A
 {
-    public function contraVariantArguments(ParentType $type)
+    /**
+     * @param ParentType $type
+     */
+    public function contraVariantArguments($type)
     { /* … */ }
 }
```

<br>

### DowngradeCovariantReturnTypeRector

Make method return same type as parent

- class: `Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector`

```diff
 class ParentType {}
 class ChildType extends ParentType {}

 class A
 {
     public function covariantReturnTypes(): ParentType
     { /* … */ }
 }

 class B extends A
 {
-    public function covariantReturnTypes(): ChildType
+    /**
+     * @return ChildType
+     */
+    public function covariantReturnTypes(): ParentType
     { /* … */ }
 }
```

<br>

### DowngradeFreadFwriteFalsyToNegationRector

Changes `fread()` or `fwrite()` compare to false to negation check

- class: `Rector\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector`

```diff
-fread($handle, $length) === false;
-fwrite($fp, '1') === false;
+!fread($handle, $length);
+!fwrite($fp, '1');
```

<br>

### DowngradeNullCoalescingOperatorRector

Remove null coalescing operator ??=

- class: `Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector`

```diff
 $array = [];
-$array['user_id'] ??= 'value';
+$array['user_id'] = $array['user_id'] ?? 'value';
```

<br>

### DowngradeNumericLiteralSeparatorRector

Remove "_" as thousands separator in numbers

- class: `Rector\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector`

```diff
 class SomeClass
 {
     public function run()
     {
-        $int = 1_000;
-        $float = 1_000_500.001;
+        $int = 1000;
+        $float = 1000500.001;
     }
 }
```

<br>

### DowngradeReturnSelfTypeDeclarationRector

Remove "self" return type, add a `"@return` self" tag instead

- class: `Rector\DowngradePhp74\Rector\ClassMethod\DowngradeReturnSelfTypeDeclarationRector`

```diff
 class A
 {
-    public function foo(): self
+    public function foo()
     {
         return $this;
     }
 }
```

<br>

### DowngradeStripTagsCallWithArrayRector

Convert 2nd param to `strip_tags` from array to string

- class: `Rector\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector`

```diff
 class SomeClass
 {
     public function run($string)
     {
         // Arrays: change to string
-        strip_tags($string, ['a', 'p']);
+        strip_tags($string, '<' . implode('><', ['a', 'p']) . '>');

         // Variables/consts/properties: if array, change to string
         $tags = ['a', 'p'];
-        strip_tags($string, $tags);
+        strip_tags($string, $tags !== null && is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);

         // Default case (eg: function call): externalize to var, then if array, change to string
-        strip_tags($string, getTags());
+        $expr = getTags();
+        strip_tags($string, is_array($expr) ? '<' . implode('><', $expr) . '>' : $expr);
     }
 }
```

<br>

### DowngradeTypedPropertyRector

Changes property type definition from type definitions to `@var` annotations.

- class: `Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector`

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

<br>

## DowngradePhp80

### DowngradeClassOnObjectToGetClassRector

Change `$object::class` to get_class($object)

- class: `Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector`

```diff
 class SomeClass
 {
     public function run($object)
     {
-        return $object::class;
+        return get_class($object);
     }
 }
```

<br>

### DowngradeMatchToSwitchRector

Downgrade `match()` to `switch()`

- class: `Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector`

```diff
 class SomeClass
 {
     public function run()
     {
-        $message = match ($statusCode) {
-            200, 300 => null,
-            400 => 'not found',
-            default => 'unknown status code',
-        };
+        switch ($statusCode) {
+            case 200:
+            case 300:
+                $message = null;
+                break;
+            case 400:
+                $message = 'not found';
+                break;
+            default:
+                $message = 'unknown status code';
+                break;
+        }
     }
 }
```

<br>

### DowngradeNonCapturingCatchesRector

Downgrade catch () without variable to one

- class: `Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector`

```diff
 class SomeClass
 {
     public function run()
     {
         try {
             // code
-        } catch (\Exception) {
+        } catch (\Exception $exception) {
             // error
         }
     }
 }
```

<br>

### DowngradeNullsafeToTernaryOperatorRector

Change nullsafe operator to ternary operator rector

- class: `Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector`

```diff
-$dateAsString = $booking->getStartDate()?->asDateTimeString();
-$dateAsString = $booking->startDate?->dateTimeString;
+$dateAsString = $booking->getStartDate() ? $booking->getStartDate()->asDateTimeString() : null;
+$dateAsString = $booking->startDate ? $booking->startDate->dateTimeString : null;
```

<br>

### DowngradeParamMixedTypeDeclarationRector

Remove the 'PHPStan\Type\MixedType' param type, add a `@param` tag instead

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector`

```diff
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

<br>

### DowngradePropertyPromotionRector

Change constructor property promotion to property asssign

- class: `Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector`

```diff
 class SomeClass
 {
-    public function __construct(public float $value = 0.0)
+    public float $value;
+
+    public function __construct(float $value = 0.0)
     {
+        $this->value = $value;
     }
 }
```

<br>

### DowngradeReturnMixedTypeDeclarationRector

Remove "mixed" return type, add a `"@return` mixed" tag instead

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector`

```diff
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

<br>

### DowngradeReturnStaticTypeDeclarationRector

Remove "static" return type, add a `"@return` `$this"` tag instead

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector`

```diff
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

<br>

### DowngradeTrailingCommasInParamUseRector

Remove trailing commas in param or use list

- class: `Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector`

```diff
 class SomeClass
 {
-    public function __construct(string $value1, string $value2,)
+    public function __construct(string $value1, string $value2)
     {
-        function (string $value1, string $value2,) {
+        function (string $value1, string $value2) {
         };

-        function () use ($value1, $value2,) {
+        function () use ($value1, $value2) {
         };
     }
 }

-function inFunction(string $value1, string $value2,)
+function inFunction(string $value1, string $value2)
 {
 }
```

<br>

### DowngradeUnionTypeParamDeclarationRector

Remove the union type params, add `@param` tags instead

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector`

```diff
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

<br>

### DowngradeUnionTypeReturnDeclarationRector

Remove returning union types, add a `@return` tag instead

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector`

```diff
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

<br>

### DowngradeUnionTypeTypedPropertyRector

Removes union type property type definition, adding `@var` annotations instead.

- class: `Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector`

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

<br>

## EarlyReturn

### ChangeAndIfToEarlyReturnRector

Changes if && to early return

- class: `Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector`

```diff
 class SomeClass
 {
     public function canDrive(Car $car)
     {
-        if ($car->hasWheels && $car->hasFuel) {
-            return true;
+        if (!$car->hasWheels) {
+            return false;
         }

-        return false;
+        if (!$car->hasFuel) {
+            return false;
+        }
+
+        return true;
     }
 }
```

<br>

### ChangeIfElseValueAssignToEarlyReturnRector

Change if/else value to early return

- class: `Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector`

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

<br>

### ChangeNestedForeachIfsToEarlyContinueRector

Change nested ifs to foreach with continue

- class: `Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector`

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

<br>

### ChangeNestedIfsToEarlyReturnRector

Change nested ifs to early return

- class: `Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector`

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

<br>

### ChangeOrIfContinueToMultiContinueRector

Changes if && to early return

- class: `Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector`

```diff
 class SomeClass
 {
     public function canDrive(Car $newCar)
     {
         foreach ($cars as $car) {
-            if ($car->hasWheels() || $car->hasFuel()) {
+            if ($car->hasWheels()) {
+                continue;
+            }
+            if ($car->hasFuel()) {
                 continue;
             }

             $car->setWheel($newCar->wheel);
             $car->setFuel($newCar->fuel);
         }
     }
 }
```

<br>

### ChangeOrIfReturnToEarlyReturnRector

Changes if || with return to early return

- class: `Rector\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector`

```diff
 class SomeClass
 {
     public function run($a, $b)
     {
-        if ($a || $b) {
+        if ($a) {
+            return null;
+        }
+        if ($b) {
             return null;
         }

         return 'another';
     }
 }
```

<br>

### RemoveAlwaysElseRector

Split if statement, when if condition always break execution flow

- class: `Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector`

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

<br>

### ReturnBinaryAndToEarlyReturnRector

Changes Single return of && && to early returns

- class: `Rector\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector`

```diff
 class SomeClass
 {
     public function accept($something, $somethingelse)
     {
-        return $something && $somethingelse;
+        if (!$something) {
+            return false;
+        }
+        return (bool) $somethingelse;
     }
 }
```

<br>

## Generic

### AddInterfaceByTraitRector

Add interface by used trait

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\AddInterfaceByTraitRector`

```php
use Rector\Generic\Rector\Class_\AddInterfaceByTraitRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddInterfaceByTraitRector::class)
        ->call('configure', [[
            AddInterfaceByTraitRector::INTERFACE_BY_TRAIT => [
                'SomeTrait' => 'SomeInterface',
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

<br>

### AddMethodParentCallRector

Add method parent call, in case new parent method is added

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\AddMethodParentCallRector`

```php
use Rector\Generic\Rector\ClassMethod\AddMethodParentCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddMethodParentCallRector::class)
        ->call('configure', [[
            AddMethodParentCallRector::METHODS_BY_PARENT_TYPES => [
                'ParentClassWithNewConstructor' => '__construct',
            ],
        ]]);
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

<br>

### AddPropertyByParentRector

Add dependency via constructor by parent class type

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\AddPropertyByParentRector`

```php
use Rector\Generic\Rector\Class_\AddPropertyByParentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddPropertyByParentRector::class)
        ->call('configure', [[
            AddPropertyByParentRector::PARENT_DEPENDENCIES => [
                'SomeParentClass' => ['SomeDependency'],
            ],
        ]]);
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

<br>

### AnnotatedPropertyInjectToConstructorInjectionRector

Turns non-private properties with `@inject` to private properties and constructor injection

- class: `Rector\Generic\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector`

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

<br>

### ArgumentAdderRector

This Rector adds new default arguments in calls of defined methods and class types.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\ArgumentAdderRector`

```php
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::ADDED_ARGUMENTS => ValueObjectInliner::inline([
                new ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', 'true', 'SomeType', null),
            ]),
        ]]);
};
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->someMethod();
+$someObject->someMethod(true);
```

<br>

```php
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::ADDED_ARGUMENTS => ValueObjectInliner::inline([
                new ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', 'true', 'SomeType', null),
            ]),
        ]]);
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

<br>

### ArgumentDefaultValueReplacerRector

Replaces defined map of arguments in defined methods and their calls.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector`

```php
use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\ValueObject\ArgumentDefaultValueReplacer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentDefaultValueReplacerRector::class)
        ->call('configure', [[
            ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => ValueObjectInliner::inline([
                new ArgumentDefaultValueReplacer('SomeExampleClass', 'someMethod', 0, 'SomeClass::OLD_CONSTANT', 'false'),
            ]),
        ]]);
};
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(SomeClass::OLD_CONSTANT);
+$someObject->someMethod(false);'
```

<br>

### InjectAnnotationClassRector

Changes properties with specified annotations class to constructor injection

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Property\InjectAnnotationClassRector`

```php
use Rector\Generic\Rector\Property\InjectAnnotationClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(InjectAnnotationClassRector::class)
        ->call('configure', [[
            InjectAnnotationClassRector::ANNOTATION_CLASSES => [
                'DI\Annotation\Inject',
                'JMS\DiExtraBundle\Annotation\Inject',
            ],
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

<br>

### MergeInterfacesRector

Merges old interface to a new one, that already has its methods

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\MergeInterfacesRector`

```php
use Rector\Generic\Rector\Class_\MergeInterfacesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MergeInterfacesRector::class)
        ->call('configure', [[
            MergeInterfacesRector::OLD_TO_NEW_INTERFACES => [
                'SomeOldInterface' => 'SomeInterface',
            ],
        ]]);
};
```

↓

```diff
-class SomeClass implements SomeInterface, SomeOldInterface
+class SomeClass implements SomeInterface
 {
 }
```

<br>

### NormalToFluentRector

Turns fluent interface calls to classic ones.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\NormalToFluentRector`

```php
use Rector\Generic\Rector\ClassMethod\NormalToFluentRector;
use Rector\Generic\ValueObject\NormalToFluent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NormalToFluentRector::class)
        ->call('configure', [[
            NormalToFluentRector::CALLS_TO_FLUENT => ValueObjectInliner::inline([
                new NormalToFluent('SomeClass', ['someFunction', 'otherFunction']), ]
            ),
        ]]);
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

<br>

### SingleToManyMethodRector

Change method that returns single value to multiple values

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\SingleToManyMethodRector`

```php
use Rector\Generic\Rector\ClassMethod\SingleToManyMethodRector;
use Rector\Generic\ValueObject\SingleToManyMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SingleToManyMethodRector::class)
        ->call('configure', [[
            SingleToManyMethodRector::SINGLES_TO_MANY_METHODS => ValueObjectInliner::inline([
                new SingleToManyMethod('SomeClass', 'getNode', 'getNodes'),
            ]),
        ]]);
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

<br>

### SwapFuncCallArgumentsRector

Swap arguments in function calls

:wrench: **configure it!**

- class: `Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector`

```php
use Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector;
use Rector\Generic\ValueObject\SwapFuncCallArguments;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SwapFuncCallArgumentsRector::class)
        ->call('configure', [[
            SwapFuncCallArgumentsRector::FUNCTION_ARGUMENT_SWAPS => ValueObjectInliner::inline([
                new SwapFuncCallArguments('some_function', [1, 0]), ]
            ),
        ]]);
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

<br>

### WrapReturnRector

Wrap return value of specific method

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\WrapReturnRector`

```php
use Rector\Generic\Rector\ClassMethod\WrapReturnRector;
use Rector\Generic\ValueObject\WrapReturn;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(WrapReturnRector::class)
        ->call('configure', [[
            WrapReturnRector::TYPE_METHOD_WRAPS => ValueObjectInliner::inline([new WrapReturn('SomeClass', 'getItem', true)]),
        ]]);
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

<br>

## Generics

### GenericsPHPStormMethodAnnotationRector

Complete PHPStorm `@method` annotations, to make it understand the PHPStan/Psalm generics

- class: `Rector\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector`

```diff
 /**
  * @template TEntity as object
  */
 abstract class AbstractRepository
 {
     /**
      * @return TEntity
      */
     public function find($id)
     {
     }
 }

 /**
  * @template TEntity as SomeObject
  * @extends AbstractRepository<TEntity>
+ * @method SomeObject find($id)
  */
 final class AndroidDeviceRepository extends AbstractRepository
 {
 }
```

<br>

## Laravel

### AddGuardToLoginEventRector

Add new `$guard` argument to Illuminate\Auth\Events\Login

- class: `Rector\Laravel\Rector\New_\AddGuardToLoginEventRector`

```diff
 use Illuminate\Auth\Events\Login;

 final class SomeClass
 {
     public function run(): void
     {
-        $loginEvent = new Login('user', false);
+        $guard = config('auth.defaults.guard');
+        $loginEvent = new Login($guard, 'user', false);
     }
 }
```

<br>

### AddMockConsoleOutputFalseToConsoleTestsRector

Add "$this->mockConsoleOutput = false"; to console tests that work with output content

- class: `Rector\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector`

```diff
 use Illuminate\Support\Facades\Artisan;
 use Illuminate\Foundation\Testing\TestCase;

 final class SomeTest extends TestCase
 {
+    public function setUp(): void
+    {
+        parent::setUp();
+
+        $this->mockConsoleOutput = false;
+    }
+
     public function test(): void
     {
         $this->assertEquals('content', \trim((new Artisan())::output()));
     }
 }
```

<br>

### AddParentBootToModelClassMethodRector

Add `parent::boot();` call to `boot()` class method in child of Illuminate\Database\Eloquent\Model

- class: `Rector\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector`

```diff
 use Illuminate\Database\Eloquent\Model;

 class Product extends Model
 {
     public function boot()
     {
+        parent::boot();
     }
 }
```

<br>

### CallOnAppArrayAccessToStandaloneAssignRector

Replace magical call on `$this->app["something"]` to standalone type assign variable

- class: `Rector\Laravel\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector`

```diff
 class SomeClass
 {
     /**
      * @var \Illuminate\Contracts\Foundation\Application
      */
     private $app;

     public function run()
     {
-        $validator = $this->app['validator']->make('...');
+        /** @var \Illuminate\Validation\Factory $validationFactory */
+        $validationFactory = $this->app['validator'];
+        $validator = $validationFactory->make('...');
     }
 }
```

<br>

### ChangeQueryWhereDateValueWithCarbonRector

Add `parent::boot();` call to `boot()` class method in child of Illuminate\Database\Eloquent\Model

- class: `Rector\Laravel\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector`

```diff
 use Illuminate\Database\Query\Builder;

 final class SomeClass
 {
     public function run(Builder $query)
     {
-        $query->whereDate('created_at', '<', Carbon::now());
+        $dateTime = Carbon::now();
+        $query->whereDate('created_at', '<=', $dateTime);
+        $query->whereTime('created_at', '<=', $dateTime);
     }
 }
```

<br>

### HelperFuncCallToFacadeClassRector

Change `app()` func calls to facade calls

- class: `Rector\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector`

```diff
 class SomeClass
 {
     public function run()
     {
-        return app('translator')->trans('value');
+        return \Illuminate\Support\Facades\App::get('translator')->trans('value');
     }
 }
```

<br>

### MakeTaggedPassedToParameterIterableTypeRector

Change param type to iterable, if passed one

- class: `Rector\Laravel\Rector\New_\MakeTaggedPassedToParameterIterableTypeRector`

```diff
 class AnotherClass
 {
     /**
      * @var \Illuminate\Contracts\Foundation\Application
      */
     private $app;

     public function create()
     {
         $tagged = $this->app->tagged('some_tagged');
         return new SomeClass($tagged);
     }
 }

 class SomeClass
 {
-    public function __construct(array $items)
+    public function __construct(iterable $items)
     {
     }
 }
```

<br>

### MinutesToSecondsInCacheRector

Change minutes argument to seconds in Illuminate\Contracts\Cache\Store and Illuminate\Support\Facades\Cache

- class: `Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector`

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

<br>

### PropertyDeferToDeferrableProviderToRector

Change deprecated `$defer` = true; to Illuminate\Contracts\Support\DeferrableProvider interface

- class: `Rector\Laravel\Rector\Class_\PropertyDeferToDeferrableProviderToRector`

```diff
 use Illuminate\Support\ServiceProvider;
+use Illuminate\Contracts\Support\DeferrableProvider;

-final class SomeServiceProvider extends ServiceProvider
+final class SomeServiceProvider extends ServiceProvider implements DeferrableProvider
 {
-    /**
-     * @var bool
-     */
-    protected $defer = true;
 }
```

<br>

### Redirect301ToPermanentRedirectRector

Change "redirect" call with 301 to "permanentRedirect"

- class: `Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector`

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

<br>

### RequestStaticValidateToInjectRector

Change static `validate()` method to `$request->validate()`

- class: `Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector`

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

<br>

## Legacy

### AddTopIncludeRector

Adds an include file at the top of matching files, except class definitions

:wrench: **configure it!**

- class: `Rector\Legacy\Rector\FileWithoutNamespace\AddTopIncludeRector`

```php
use Rector\Legacy\Rector\FileWithoutNamespace\AddTopIncludeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddTopIncludeRector::class)
        ->call('configure', [[
            AddTopIncludeRector::AUTOLOAD_FILE_PATH => '/../autoloader.php',
            AddTopIncludeRector::PATTERNS => ['pat*/*/?ame.php', 'somepath/?ame.php'],
        ]]);
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

<br>

### ChangeSingletonToServiceRector

Change singleton class to normal class that can be registered as a service

- class: `Rector\Legacy\Rector\Class_\ChangeSingletonToServiceRector`

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

<br>

### FunctionToStaticMethodRector

Change functions to static calls, so composer can autoload them

- class: `Rector\Legacy\Rector\FileWithoutNamespace\FunctionToStaticMethodRector`

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

<br>

### RemoveIncludeRector

Remove includes (include, include_once, require, require_once) from source

- class: `Rector\Legacy\Rector\Include_\RemoveIncludeRector`

```diff
-include 'somefile.php';
```

<br>

## MockeryToProphecy

### MockeryCloseRemoveRector

Removes mockery close from test classes

- class: `Rector\MockeryToProphecy\Rector\StaticCall\MockeryCloseRemoveRector`

```diff
 public function tearDown() : void
 {
-    \Mockery::close();
 }
```

<br>

### MockeryCreateMockToProphizeRector

Changes mockery mock creation to Prophesize

- class: `Rector\MockeryToProphecy\Rector\ClassMethod\MockeryCreateMockToProphizeRector`

```diff
-$mock = \Mockery::mock('MyClass');
+ $mock = $this->prophesize('MyClass');
+
 $service = new Service();
-$service->injectDependency($mock);
+$service->injectDependency($mock->reveal());
```

<br>

## MockistaToMockery

### MockeryTearDownRector

Add `Mockery::close()` in `tearDown()` method if not yet

- class: `Rector\MockistaToMockery\Rector\Class_\MockeryTearDownRector`

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

<br>

### MockistaMockToMockeryMockRector

Change functions to static calls, so composer can autoload them

- class: `Rector\MockistaToMockery\Rector\ClassMethod\MockistaMockToMockeryMockRector`

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

<br>

## MysqlToMysqli

### MysqlAssignToMysqliRector

Converts more complex mysql functions to mysqli

- class: `Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector`

```diff
-$data = mysql_db_name($result, $row);
+mysqli_data_seek($result, $row);
+$fetch = mysql_fetch_row($result);
+$data = $fetch[0];
```

<br>

### MysqlFuncCallToMysqliRector

Converts more complex mysql functions to mysqli

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector`

```diff
-mysql_drop_db($database);
+mysqli_query('DROP DATABASE ' . $database);
```

<br>

### MysqlPConnectToMysqliConnectRector

Replace `mysql_pconnect()` with `mysqli_connect()` with host p: prefix

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector`

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

<br>

### MysqlQueryMysqlErrorWithLinkRector

Add mysql_query and mysql_error with connection

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector`

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

<br>

## Naming

### MakeBoolPropertyRespectIsHasWasMethodNamingRector

Renames property to respect is/has/was method naming

- class: `Rector\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector`

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

<br>

### MakeGetterClassMethodNameStartWithGetRector

Change getter method names to start with get/provide

- class: `Rector\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector`

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

<br>

### MakeIsserClassMethodNameStartWithIsRector

Change is method names to start with is/has/was

- class: `Rector\Naming\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector`

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

<br>

### RenameForeachValueVariableToMatchMethodCallReturnTypeRector

Renames value variable name in foreach loop to match method type

- class: `Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector`

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

<br>

### RenameParamToMatchTypeRector

Rename variable to match new ClassType

- class: `Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector`

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

<br>

### RenamePropertyToMatchTypeRector

Rename property and method param to match its type

- class: `Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector`

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

<br>

### RenameVariableToMatchMethodCallReturnTypeRector

Rename variable to match method return type

- class: `Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector`

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

<br>

### RenameVariableToMatchNewTypeRector

Rename variable to match new ClassType

- class: `Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector`

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

<br>

### UnderscoreToCamelCaseLocalVariableNameRector

Change under_score local variable names to camelCase

- class: `Rector\Naming\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector`

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

<br>

### UnderscoreToCamelCasePropertyNameRector

Change under_score names to camelCase

- class: `Rector\Naming\Rector\Property\UnderscoreToCamelCasePropertyNameRector`

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

<br>

### UnderscoreToCamelCaseVariableNameRector

Change under_score names to camelCase

- class: `Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector`

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

<br>

## Nette

### AddNextrasDatePickerToDateControlRector

Nextras/Form upgrade of addDatePicker method call to DateControl assign

- class: `Rector\Nette\Rector\MethodCall\AddNextrasDatePickerToDateControlRector`

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

<br>

### BuilderExpandToHelperExpandRector

Change `containerBuilder->expand()` to static call with parameters

- class: `Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector`

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

<br>

### ContextGetByTypeToConstructorInjectionRector

Move dependency get via `$context->getByType()` to constructor injection

- class: `Rector\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector`

```diff
 class SomeClass
 {
     /**
      * @var \Nette\DI\Container
      */
     private $context;

+    public function __construct(private SomeTypeToInject $someTypeToInject)
+    {
+    }
+
     public function run()
     {
-        $someTypeToInject = $this->context->getByType(SomeTypeToInject::class);
+        $someTypeToInject = $this->someTypeToInject;
     }
 }
```

<br>

### ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector

convert `addUpload()` with 3rd argument true to `addMultiUpload()`

- class: `Rector\Nette\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector`

```diff
 $form = new Nette\Forms\Form();
-$form->addUpload('...', '...', true);
+$form->addMultiUpload('...', '...');
```

<br>

### EndsWithFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings::endsWith()` over bare string-functions

- class: `Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector`

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

<br>

### FilePutContentsToFileSystemWriteRector

Change `file_put_contents()` to `FileSystem::write()`

- class: `Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector`

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

<br>

### JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector

Changes `json_encode()/json_decode()` to safer and more verbose `Nette\Utils\Json::encode()/decode()` calls

- class: `Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector`

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

<br>

### MagicHtmlCallToAppendAttributeRector

Change magic `addClass()` etc. calls on Html to explicit methods

- class: `Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector`

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

<br>

### MergeDefaultsInGetConfigCompilerExtensionRector

Change `$this->getConfig($defaults)` to `array_merge`

- class: `Rector\Nette\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector`

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

<br>

### MoveFinalGetUserToCheckRequirementsClassMethodRector

Presenter method `getUser()` is now final, move logic to `checkRequirements()`

- class: `Rector\Nette\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector`

```diff
 use Nette\Application\UI\Presenter;

 class SomeControl extends Presenter
 {
-    public function getUser()
+    public function checkRequirements()
     {
-        $user = parent::getUser();
+        $user = $this->getUser();
         $user->getStorage()->setNamespace('admin_session');
-        return $user;
+
+        parent::checkRequirements();
     }
 }
```

<br>

### PregFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings` over bare `preg_split()` and `preg_replace()` functions

- class: `Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector`

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

<br>

### PregMatchFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings` over bare `preg_match()` and `preg_match_all()` functions

- class: `Rector\Nette\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector`

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

<br>

### RemoveParentAndNameFromComponentConstructorRector

Remove `$parent` and `$name` in control constructor

- class: `Rector\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector`

```diff
 use Nette\Application\UI\Control;

 class SomeControl extends Control
 {
-    public function __construct(IContainer $parent = null, $name = null, int $value)
+    public function __construct(int $value)
     {
-        parent::__construct($parent, $name);
         $this->value = $value;
     }
 }
```

<br>

### RequestGetCookieDefaultArgumentToCoalesceRector

Add removed `Nette\Http\Request::getCookies()` default value as coalesce

- class: `Rector\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector`

```diff
 use Nette\Http\Request;

 class SomeClass
 {
     public function run(Request $request)
     {
-        return $request->getCookie('name', 'default');
+        return $request->getCookie('name') ?? 'default';
     }
 }
```

<br>

### SetClassWithArgumentToSetFactoryRector

Change setClass with class and arguments to separated methods

- class: `Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector`

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

<br>

### StartsWithFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings::startsWith()` over bare string-functions

- class: `Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector`

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

<br>

### StrposToStringsContainsRector

Use `Nette\Utils\Strings` over bare string-functions

- class: `Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector`

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

<br>

### SubstrStrlenFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings` over bare string-functions

- class: `Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector`

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

<br>

### TemplateMagicAssignToExplicitVariableArrayRector

Change `$this->templates->{magic}` to `$this->template->render(..., $values)` in components

- class: `Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector`

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

<br>

### TranslateClassMethodToVariadicsRector

Change `translate()` method call 2nd arg to variadic

- class: `Rector\Nette\Rector\ClassMethod\TranslateClassMethodToVariadicsRector`

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

<br>

## NetteCodeQuality

### AnnotateMagicalControlArrayAccessRector

Change magic `$this["some_component"]` to variable assign with `@var` annotation

- class: `Rector\NetteCodeQuality\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector`

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

<br>

### ArrayAccessGetControlToGetComponentMethodCallRector

Change magic arrays access get, to explicit `$this->getComponent(...)` method

- class: `Rector\NetteCodeQuality\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector`

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

<br>

### ArrayAccessSetControlToAddComponentMethodCallRector

Change magic arrays access set, to explicit `$this->setComponent(...)` method

- class: `Rector\NetteCodeQuality\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector`

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

<br>

### ChangeFormArrayAccessToAnnotatedControlVariableRector

Change array access magic on `$form` to explicit standalone typed variable

- class: `Rector\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector`

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

<br>

### MakeGetComponentAssignAnnotatedRector

Add doc type for magic `$control->getComponent(...)` assign

- class: `Rector\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector`

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

<br>

### MergeTemplateSetFileToTemplateRenderRector

Change `$this->template->setFile()` `$this->template->render()`

- class: `Rector\NetteCodeQuality\Rector\ClassMethod\MergeTemplateSetFileToTemplateRenderRector`

```diff
 use Nette\Application\UI\Control;

 final class SomeControl extends Control
 {
     public function render()
     {
-        $this->template->setFile(__DIR__ . '/someFile.latte');
-        $this->template->render();
+        $this->template->render(__DIR__ . '/someFile.latte');
     }
 }
```

<br>

### MoveInjectToExistingConstructorRector

Move `@inject` properties to constructor, if there already is one

- class: `Rector\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector`

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

<br>

### SubstrMinusToStringEndsWithRector

Change `substr` function with minus to `Strings::endsWith()`

- class: `Rector\NetteCodeQuality\Rector\Identical\SubstrMinusToStringEndsWithRector`

```diff
-substr($var, -4) !== 'Test';
-substr($var, -4) === 'Test';
+! \Nette\Utils\Strings::endsWith($var, 'Test');
+\Nette\Utils\Strings::endsWith($var, 'Test');
```

<br>

## NetteKdyby

### ChangeNetteEventNamesInGetSubscribedEventsRector

Change EventSubscriber from Kdyby to Contributte

- class: `Rector\NetteKdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector`

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

<br>

### ReplaceEventManagerWithEventSubscriberRector

Change Kdyby EventManager to EventDispatcher

- class: `Rector\NetteKdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector`

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

<br>

### ReplaceMagicPropertyEventWithEventClassRector

Change `$onProperty` magic call with event disptacher and class dispatch

- class: `Rector\NetteKdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector`

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

<br>

### ReplaceMagicPropertyWithEventClassRector

Change `getSubscribedEvents()` from on magic property, to Event class

- class: `Rector\NetteKdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector`

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

<br>

## NetteTesterToPHPUnit

### NetteAssertToPHPUnitAssertRector

Migrate Nette/Assert calls to PHPUnit

- class: `Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector`

```diff
 use Tester\Assert;

 function someStaticFunctions()
 {
-    Assert::true(10 == 5);
+    \PHPUnit\Framework\Assert::assertTrue(10 == 5);
 }
```

<br>

### NetteTesterClassToPHPUnitClassRector

Migrate Nette Tester test case to PHPUnit

- class: `Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector`

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

<br>

### RenameTesterTestToPHPUnitToTestFileRector

Rename "*.phpt" file to "*Test.php" file

- class: `Rector\NetteTesterToPHPUnit\Rector\FileNode\RenameTesterTestToPHPUnitToTestFileRector`

```diff
-// tests/SomeTestCase.phpt
+// tests/SomeTestCase.php
```

<br>

## NetteToSymfony

### DeleteFactoryInterfaceRector

Interface factories are not needed in Symfony. Clear constructor injection is used instead

- class: `Rector\NetteToSymfony\Rector\Interface_\DeleteFactoryInterfaceRector`

```diff
-interface SomeControlFactoryInterface
-{
-    public function create();
-}
```

<br>

### FormControlToControllerAndFormTypeRector

Change Form that extends Control to Controller and decoupled FormType

- class: `Rector\NetteToSymfony\Rector\Class_\FormControlToControllerAndFormTypeRector`

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

Extra file:

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SomeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('name', TextType::class, [
            'label' => 'Your name',
        ]);
    }
}
```

<br>

### FromHttpRequestGetHeaderToHeadersGetRector

Changes `getHeader()` to `$request->headers->get()`

- class: `Rector\NetteToSymfony\Rector\MethodCall\FromHttpRequestGetHeaderToHeadersGetRector`

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

<br>

### FromRequestGetParameterToAttributesGetRector

Changes `"getParameter()"` to `"attributes->get()"` from Nette to Symfony

- class: `Rector\NetteToSymfony\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector`

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

<br>

### NetteControlToSymfonyControllerRector

Migrate Nette Component to Symfony Controller

- class: `Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector`

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

<br>

### NetteFormToSymfonyFormRector

Migrate Nette\Forms in Presenter to Symfony

- class: `Rector\NetteToSymfony\Rector\MethodCall\NetteFormToSymfonyFormRector`

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

<br>

### RenameEventNamesInEventSubscriberRector

Changes event names from Nette ones to Symfony ones

- class: `Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector`

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

<br>

### RouterListToControllerAnnotationsRector

Change new `Route()` from RouteFactory to `@Route` annotation above controller method

- class: `Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector`

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

<br>

### WrapTransParameterNameRector

Adds %% to placeholder name of `trans()` method if missing

- class: `Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector`

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

<br>

## NetteUtilsCodeQuality

### ReplaceTimeNumberWithDateTimeConstantRector

Replace `time` numbers with `Nette\Utils\DateTime` constants

- class: `Rector\NetteUtilsCodeQuality\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector`

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

<br>

## Order

### OrderClassConstantsByIntegerValueRector

Order class constant order by their integer value

- class: `Rector\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector`

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

<br>

### OrderConstantsByVisibilityRector

Orders constants by visibility

- class: `Rector\Order\Rector\Class_\OrderConstantsByVisibilityRector`

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

<br>

### OrderFirstLevelClassStatementsRector

Orders first level Class statements

- class: `Rector\Order\Rector\Class_\OrderFirstLevelClassStatementsRector`

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

<br>

### OrderMethodsByVisibilityRector

Orders method by visibility

- class: `Rector\Order\Rector\Class_\OrderMethodsByVisibilityRector`

```diff
 class SomeClass
 {
+    public function publicFunctionName();
     protected function protectedFunctionName();
     private function privateFunctionName();
-    public function publicFunctionName();
 }
```

<br>

### OrderPrivateMethodsByUseRector

Order private methods in order of their use

- class: `Rector\Order\Rector\Class_\OrderPrivateMethodsByUseRector`

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

<br>

### OrderPropertiesByVisibilityRector

Orders properties by visibility

- class: `Rector\Order\Rector\Class_\OrderPropertiesByVisibilityRector`

```diff
 final class SomeClass
 {
+    public $publicProperty;
     protected $protectedProperty;
     private $privateProperty;
-    public $publicProperty;
 }
```

<br>

## PHPOffice

### AddRemovedDefaultValuesRector

Complete removed default values explicitly

- class: `Rector\PHPOffice\Rector\StaticCall\AddRemovedDefaultValuesRector`

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

<br>

### CellStaticToCoordinateRector

Methods to manipulate coordinates that used to exists in PHPExcel_Cell to PhpOffice\PhpSpreadsheet\Cell\Coordinate

- class: `Rector\PHPOffice\Rector\StaticCall\CellStaticToCoordinateRector`

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

<br>

### ChangeChartRendererRector

Change chart renderer

- class: `Rector\PHPOffice\Rector\StaticCall\ChangeChartRendererRector`

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

<br>

### ChangeConditionalGetConditionRector

Change argument `PHPExcel_Style_Conditional->getCondition()` to `getConditions()`

- class: `Rector\PHPOffice\Rector\MethodCall\ChangeConditionalGetConditionRector`

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

<br>

### ChangeConditionalReturnedCellRector

Change conditional call to `getCell()`

- class: `Rector\PHPOffice\Rector\MethodCall\ChangeConditionalReturnedCellRector`

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

<br>

### ChangeConditionalSetConditionRector

Change argument `PHPExcel_Style_Conditional->setCondition()` to `setConditions()`

- class: `Rector\PHPOffice\Rector\MethodCall\ChangeConditionalSetConditionRector`

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

<br>

### ChangeDataTypeForValueRector

Change argument `DataType::dataTypeForValue()` to DefaultValueBinder

- class: `Rector\PHPOffice\Rector\StaticCall\ChangeDataTypeForValueRector`

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

<br>

### ChangeDuplicateStyleArrayToApplyFromArrayRector

Change method call `duplicateStyleArray()` to `getStyle()` + `applyFromArray()`

- class: `Rector\PHPOffice\Rector\MethodCall\ChangeDuplicateStyleArrayToApplyFromArrayRector`

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

<br>

### ChangeIOFactoryArgumentRector

Change argument of `PHPExcel_IOFactory::createReader()`, `PHPExcel_IOFactory::createWriter()` and `PHPExcel_IOFactory::identify()`

- class: `Rector\PHPOffice\Rector\StaticCall\ChangeIOFactoryArgumentRector`

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

<br>

### ChangePdfWriterRector

Change init of PDF writer

- class: `Rector\PHPOffice\Rector\StaticCall\ChangePdfWriterRector`

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

<br>

### ChangeSearchLocationToRegisterReaderRector

Change argument `addSearchLocation()` to `registerReader()`

- class: `Rector\PHPOffice\Rector\StaticCall\ChangeSearchLocationToRegisterReaderRector`

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

<br>

### GetDefaultStyleToGetParentRector

Methods to (new `Worksheet())->getDefaultStyle()` to `getParent()->getDefaultStyle()`

- class: `Rector\PHPOffice\Rector\MethodCall\GetDefaultStyleToGetParentRector`

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

<br>

### IncreaseColumnIndexRector

Column index changed from 0 to 1 - run only ONCE! changes current value without memory

- class: `Rector\PHPOffice\Rector\MethodCall\IncreaseColumnIndexRector`

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

<br>

### RemoveSetTempDirOnExcelWriterRector

Remove `setTempDir()` on PHPExcel_Writer_Excel5

- class: `Rector\PHPOffice\Rector\MethodCall\RemoveSetTempDirOnExcelWriterRector`

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

<br>

## PHPUnit

### AddDoesNotPerformAssertionToNonAssertingTestRector

Tests without assertion will have `@doesNotPerformAssertion`

- class: `Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector`

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
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

<br>

### AddProphecyTraitRector

Add Prophecy trait for method using `$this->prophesize()`

- class: `Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector`

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

<br>

### AddSeeTestAnnotationRector

Add `@see` annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.

- class: `Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector`

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

<br>

### ArrayArgumentToDataProviderRector

Move array argument from tests into data provider [configurable]

:wrench: **configure it!**

- class: `Rector\PHPUnit\Rector\Class_\ArrayArgumentToDataProviderRector`

```php
use Rector\PHPUnit\Rector\Class_\ArrayArgumentToDataProviderRector;
use Rector\PHPUnit\ValueObject\ArrayArgumentToDataProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayArgumentToDataProviderRector::class)
        ->call('configure', [[
            ArrayArgumentToDataProviderRector::ARRAY_ARGUMENTS_TO_DATA_PROVIDERS => ValueObjectInliner::inline([
                new ArrayArgumentToDataProvider('PHPUnit\Framework\TestCase', 'doTestMultiple', 'doTestSingle', 'number'),
            ]),
        ]]);
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

<br>

### AssertCompareToSpecificMethodRector

Turns vague php-only method in PHPUnit TestCase to more specific

- class: `Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector`

```diff
-$this->assertSame(10, count($anything), "message");
+$this->assertCount(10, $anything, "message");
```

<br>

```diff
-$this->assertNotEquals(get_class($value), stdClass::class);
+$this->assertNotInstanceOf(stdClass::class, $value);
```

<br>

### AssertComparisonToSpecificMethodRector

Turns comparison operations to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector`

```diff
-$this->assertTrue($foo === $bar, "message");
+$this->assertSame($bar, $foo, "message");
```

<br>

```diff
-$this->assertFalse($foo >= $bar, "message");
+$this->assertLessThanOrEqual($bar, $foo, "message");
```

<br>

### AssertEqualsParameterToSpecificMethodsTypeRector

Change `assertEquals()/assertNotEquals()` method parameters to new specific alternatives

- class: `Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector`

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

<br>

### AssertEqualsToSameRector

Turns `assertEquals()` into stricter `assertSame()` for scalar values in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector`

```diff
-$this->assertEquals(2, $result, "message");
+$this->assertSame(2, $result, "message");
```

<br>

```diff
-$this->assertEquals($aString, $result, "message");
+$this->assertSame($aString, $result, "message");
```

<br>

### AssertFalseStrposToContainsRector

Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector`

```diff
-$this->assertFalse(strpos($anything, "foo"), "message");
+$this->assertNotContains("foo", $anything, "message");
```

<br>

```diff
-$this->assertNotFalse(stripos($anything, "foo"), "message");
+$this->assertContains("foo", $anything, "message");
```

<br>

### AssertInstanceOfComparisonRector

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector`

```diff
-$this->assertTrue($foo instanceof Foo, "message");
+$this->assertInstanceOf("Foo", $foo, "message");
```

<br>

```diff
-$this->assertFalse($foo instanceof Foo, "message");
+$this->assertNotInstanceOf("Foo", $foo, "message");
```

<br>

### AssertIssetToSpecificMethodRector

Turns isset comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertIssetToSpecificMethodRector`

```diff
-$this->assertTrue(isset($anything->foo));
+$this->assertObjectHasAttribute("foo", $anything);
```

<br>

```diff
-$this->assertFalse(isset($anything["foo"]), "message");
+$this->assertArrayNotHasKey("foo", $anything, "message");
```

<br>

### AssertNotOperatorRector

Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertNotOperatorRector`

```diff
-$this->assertTrue(!$foo, "message");
+$this->assertFalse($foo, "message");
```

<br>

```diff
-$this->assertFalse(!$foo, "message");
+$this->assertTrue($foo, "message");
```

<br>

### AssertPropertyExistsRector

Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector`

```diff
-$this->assertTrue(property_exists(new Class, "property"), "message");
+$this->assertClassHasAttribute("property", "Class", "message");
```

<br>

```diff
-$this->assertFalse(property_exists(new Class, "property"), "message");
+$this->assertClassNotHasAttribute("property", "Class", "message");
```

<br>

### AssertRegExpRector

Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertRegExpRector`

```diff
-$this->assertSame(1, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertRegExp("/^Message for ".*"\.$/", $string, $message);
```

<br>

```diff
-$this->assertEquals(false, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertNotRegExp("/^Message for ".*"\.$/", $string, $message);
```

<br>

### AssertResourceToClosedResourceRector

Turns `assertIsNotResource()` into stricter `assertIsClosedResource()` for resource values in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertResourceToClosedResourceRector`

```diff
-$this->assertIsNotResource($aResource, "message");
+$this->assertIsClosedResource($aResource, "message");
```

<br>

### AssertSameBoolNullToSpecificMethodRector

Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector`

```diff
-$this->assertSame(null, $anything);
+$this->assertNull($anything);
```

<br>

```diff
-$this->assertNotSame(false, $anything);
+$this->assertNotFalse($anything);
```

<br>

### AssertSameTrueFalseToAssertTrueFalseRector

Change `$this->assertSame(true,` ...) to `assertTrue()`

- class: `Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector`

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     public function test()
     {
         $value = (bool) mt_rand(0, 1);
-        $this->assertSame(true, $value);
+        $this->assertTrue($value);
     }
 }
```

<br>

### AssertTrueFalseInternalTypeToSpecificMethodRector

Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector`

```diff
-$this->assertTrue(is_{internal_type}($anything), "message");
+$this->assertInternalType({internal_type}, $anything, "message");
```

<br>

```diff
-$this->assertFalse(is_{internal_type}($anything), "message");
+$this->assertNotInternalType({internal_type}, $anything, "message");
```

<br>

### AssertTrueFalseToSpecificMethodRector

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

- class: `Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector`

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

<br>

### ConstructClassMethodToSetUpTestCaseRector

Change `__construct()` method in tests of `PHPUnit\Framework\TestCase` to `setUp()`, to prevent dangerous override

- class: `Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector`

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     private $someValue;

-    public function __construct(?string $name = null, array $data = [], string $dataName = '')
+    protected function setUp()
     {
+        parent::setUp();
+
         $this->someValue = 1000;
-        parent::__construct($name, $data, $dataName);
     }
 }
```

<br>

### CreateMockToCreateStubRector

Replaces `createMock()` with `createStub()` when relevant

- class: `Rector\PHPUnit\Rector\MethodCall\CreateMockToCreateStubRector`

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

<br>

### DelegateExceptionArgumentsRector

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

- class: `Rector\PHPUnit\Rector\MethodCall\DelegateExceptionArgumentsRector`

```diff
-$this->setExpectedException(Exception::class, "Message", "CODE");
+$this->setExpectedException(Exception::class);
+$this->expectExceptionMessage('Message');
+$this->expectExceptionCode('CODE');
```

<br>

### ExceptionAnnotationRector

Changes ``@expectedException` annotations to `expectException*()` methods

- class: `Rector\PHPUnit\Rector\ClassMethod\ExceptionAnnotationRector`

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

<br>

### ExplicitPhpErrorApiRector

Use explicit API for expecting PHP errors, warnings, and notices

- class: `Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector`

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

<br>

### GetMockBuilderGetMockToCreateMockRector

Remove `getMockBuilder()` to `createMock()`

- class: `Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector`

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

<br>

### GetMockRector

Turns getMock*() methods to `createMock()`

- class: `Rector\PHPUnit\Rector\StaticCall\GetMockRector`

```diff
-$this->getMock("Class");
+$this->createMock("Class");
```

<br>

```diff
-$this->getMockWithoutInvokingTheOriginalConstructor("Class");
+$this->createMock("Class");
```

<br>

### RemoveDataProviderTestPrefixRector

Data provider methods cannot start with "test" prefix

- class: `Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector`

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

<br>

### RemoveEmptyTestMethodRector

Remove empty test methods

- class: `Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector`

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

<br>

### RemoveExpectAnyFromMockRector

Remove `expect($this->any())` from mocks as it has no added value

- class: `Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector`

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

<br>

### ReplaceAssertArraySubsetWithDmsPolyfillRector

Change `assertArraySubset()` to static call of DMS\PHPUnitExtensions\ArraySubset\Assert

- class: `Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector`

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

<br>

### SimplifyForeachInstanceOfRector

Simplify unnecessary foreach check of instances

- class: `Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector`

```diff
-foreach ($foos as $foo) {
-    $this->assertInstanceOf(SplFileInfo::class, $foo);
-}
+$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
```

<br>

### SpecificAssertContainsRector

Change `assertContains()/assertNotContains()` method to new string and iterable alternatives

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector`

```diff
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

<br>

### SpecificAssertContainsWithoutIdentityRector

Change `assertContains()/assertNotContains()` with non-strict comparison to new specific alternatives

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector`

```diff
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

<br>

### SpecificAssertInternalTypeRector

Change `assertInternalType()/assertNotInternalType()` method to new specific alternatives

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector`

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

<br>

### TestListenerToHooksRector

Refactor "*TestListener.php" to particular "*Hook.php" files

- class: `Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector`

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

<br>

### TryCatchToExpectExceptionRector

Turns try/catch to `expectException()` call

- class: `Rector\PHPUnit\Rector\ClassMethod\TryCatchToExpectExceptionRector`

```diff
-try {
-    $someService->run();
-} catch (Throwable $exception) {
-    $this->assertInstanceOf(RuntimeException::class, $e);
-    $this->assertContains('There was an error executing the following script', $e->getMessage());
-}
+$this->expectException(RuntimeException::class);
+$this->expectExceptionMessage('There was an error executing the following script');
+$someService->run();
```

<br>

### UseSpecificWillMethodRector

Changes `->will($this->xxx())` to one specific method

- class: `Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector`

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

<br>

### WithConsecutiveArgToArrayRector

Split `withConsecutive()` arg to array

- class: `Rector\PHPUnit\Rector\MethodCall\WithConsecutiveArgToArrayRector`

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

<br>

## PHPUnitSymfony

### AddMessageToEqualsResponseCodeRector

Add response content to response code assert, so it is easier to debug

- class: `Rector\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector`

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

<br>

## PSR4

### MultipleClassFileToPsr4ClassesRector

Change multiple classes in one file to standalone PSR-4 classes.

- class: `Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector`

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

<br>

### NormalizeNamespaceByPSR4ComposerAutoloadRector

Adds namespace to namespace-less files or correct namespace to match PSR-4 in `composer.json` autoload section. Run with combination with "Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector"

- class: `Rector\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector`

- with `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\CustomNamespace\\": "src"
        }
    }
}
```

↓

```diff
 // src/SomeClass.php

+namespace App\CustomNamespace;
+
 class SomeClass
 {
 }
```

<br>

## Php52

### ContinueToBreakInSwitchRector

Use break instead of continue in switch statements

- class: `Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector`

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

<br>

### VarToPublicPropertyRector

Remove unused private method

- class: `Rector\Php52\Rector\Property\VarToPublicPropertyRector`

```diff
 final class SomeController
 {
-    var $name = 'Tom';
+    public $name = 'Tom';
 }
```

<br>

## Php53

### ClearReturnNewByReferenceRector

Remove reference from "$assign = &new Value;"

- class: `Rector\Php53\Rector\AssignRef\ClearReturnNewByReferenceRector`

```diff
-$assign = &new Value;
+$assign = new Value;
```

<br>

### DirNameFileConstantToDirConstantRector

Convert dirname(__FILE__) to __DIR__

- class: `Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector`

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

<br>

### ReplaceHttpServerVarsByServerRector

Rename old `$HTTP_*` variable names to new replacements

- class: `Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector`

```diff
-$serverVars = $HTTP_SERVER_VARS;
+$serverVars = $_SERVER;
```

<br>

### TernaryToElvisRector

Use ?: instead of ?, where useful

- class: `Rector\Php53\Rector\Ternary\TernaryToElvisRector`

```diff
 function elvis()
 {
-    $value = $a ? $a : false;
+    $value = $a ?: false;
 }
```

<br>

## Php54

### RemoveReferenceFromCallRector

Remove & from function and method calls

- class: `Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector`

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

<br>

### RemoveZeroBreakContinueRector

Remove 0 from break and continue

- class: `Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector`

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

<br>

## Php55

### ClassConstantToSelfClassRector

Change `__CLASS__` to self::class

- class: `Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector`

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

<br>

### PregReplaceEModifierRector

The /e modifier is no longer supported, use `preg_replace_callback` instead

- class: `Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector`

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

<br>

### StringClassNameToClassConstantRector

Replace string class names by <class>::class constant

:wrench: **configure it!**

- class: `Rector\Php55\Rector\String_\StringClassNameToClassConstantRector`

```php
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringClassNameToClassConstantRector::class)
        ->call('configure', [[
            StringClassNameToClassConstantRector::CLASSES_TO_SKIP => ['ClassName', 'AnotherClassName'],
        ]]);
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

<br>

## Php56

### AddDefaultValueForUndefinedVariableRector

Adds default value for undefined variable

- class: `Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector`

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

<br>

### PowToExpRector

Changes pow(val, val2) to ** `(exp)` parameter

- class: `Rector\Php56\Rector\FuncCall\PowToExpRector`

```diff
-pow(1, 2);
+1**2;
```

<br>

## Php70

### BreakNotInLoopOrSwitchToReturnRector

Convert break outside for/foreach/switch context to return

- class: `Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector`

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

<br>

### CallUserMethodRector

Changes `call_user_method()/call_user_method_array()` to `call_user_func()/call_user_func_array()`

- class: `Rector\Php70\Rector\FuncCall\CallUserMethodRector`

```diff
-call_user_method($method, $obj, "arg1", "arg2");
+call_user_func(array(&$obj, "method"), "arg1", "arg2");
```

<br>

### EmptyListRector

`list()` cannot be empty

- class: `Rector\Php70\Rector\List_\EmptyListRector`

```diff
-'list() = $values;'
+'list($unusedGenerated) = $values;'
```

<br>

### EregToPregMatchRector

Changes ereg*() to preg*() calls

- class: `Rector\Php70\Rector\FuncCall\EregToPregMatchRector`

```diff
-ereg("hi")
+preg_match("#hi#");
```

<br>

### ExceptionHandlerTypehintRector

Changes property `@var` annotations from annotation to type.

- class: `Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector`

```diff
-function handler(Exception $exception) { ... }
+function handler(Throwable $exception) { ... }
 set_exception_handler('handler');
```

<br>

### IfToSpaceshipRector

Changes if/else to spaceship <=> where useful

- class: `Rector\Php70\Rector\If_\IfToSpaceshipRector`

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

<br>

### ListSplitStringRector

`list()` cannot split string directly anymore, use `str_split()`

- class: `Rector\Php70\Rector\Assign\ListSplitStringRector`

```diff
-list($foo) = "string";
+list($foo) = str_split("string");
```

<br>

### ListSwapArrayOrderRector

`list()` assigns variables in reverse order - relevant in array assign

- class: `Rector\Php70\Rector\Assign\ListSwapArrayOrderRector`

```diff
-list($a[], $a[]) = [1, 2];
+list($a[], $a[]) = array_reverse([1, 2]);
```

<br>

### MultiDirnameRector

Changes multiple `dirname()` calls to one with nesting level

- class: `Rector\Php70\Rector\FuncCall\MultiDirnameRector`

```diff
-dirname(dirname($path));
+dirname($path, 2);
```

<br>

### NonVariableToVariableOnFunctionCallRector

Transform non variable like arguments to variable where a function or method expects an argument passed by reference

- class: `Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector`

```diff
-reset(a());
+$a = a(); reset($a);
```

<br>

### Php4ConstructorRector

Changes PHP 4 style constructor to __construct.

- class: `Rector\Php70\Rector\ClassMethod\Php4ConstructorRector`

```diff
 class SomeClass
 {
-    public function SomeClass()
+    public function __construct()
     {
     }
 }
```

<br>

### RandomFunctionRector

Changes rand, `srand` and `getrandmax` by new mt_* alternatives.

- class: `Rector\Php70\Rector\FuncCall\RandomFunctionRector`

```diff
-rand();
+mt_rand();
```

<br>

### ReduceMultipleDefaultSwitchRector

Remove first default switch, that is ignored

- class: `Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector`

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

<br>

### RenameMktimeWithoutArgsToTimeRector

Renames `mktime()` without arguments to `time()`

- class: `Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector`

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

<br>

### StaticCallOnNonStaticToInstanceCallRector

Changes static call to instance call, where not useful

- class: `Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector`

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

<br>

### TernaryToNullCoalescingRector

Changes unneeded null check to ?? operator

- class: `Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector`

```diff
-$value === null ? 10 : $value;
+$value ?? 10;
```

<br>

```diff
-isset($value) ? $value : 10;
+$value ?? 10;
```

<br>

### TernaryToSpaceshipRector

Use <=> spaceship instead of ternary with same effect

- class: `Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector`

```diff
 function order_func($a, $b) {
-    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
+    return $a <=> $b;
 }
```

<br>

### ThisCallOnStaticMethodToStaticCallRector

Changes `$this->call()` to static method to static call

- class: `Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector`

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

<br>

### WrapVariableVariableNameInCurlyBracesRector

Ensure variable variables are wrapped in curly braces

- class: `Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector`

```diff
 function run($foo)
 {
-    global $$foo->bar;
+    global ${$foo->bar};
 }
```

<br>

## Php71

### AssignArrayToStringRector

String cannot be turned into array by assignment anymore

- class: `Rector\Php71\Rector\Assign\AssignArrayToStringRector`

```diff
-$string = '';
+$string = [];
 $string[] = 1;
```

<br>

### BinaryOpBetweenNumberAndStringRector

Change binary operation between some number + string to PHP 7.1 compatible version

- class: `Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector`

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

<br>

### CountOnNullRector

Changes `count()` on null to safe ternary check

- class: `Rector\Php71\Rector\FuncCall\CountOnNullRector`

```diff
 $values = null;
-$count = count($values);
+$count = count((array) $values);
```

<br>

### IsIterableRector

Changes `is_array` + Traversable check to `is_iterable`

- class: `Rector\Php71\Rector\BooleanOr\IsIterableRector`

```diff
-is_array($foo) || $foo instanceof Traversable;
+is_iterable($foo);
```

<br>

### ListToArrayDestructRector

Remove & from new &X

- class: `Rector\Php71\Rector\List_\ListToArrayDestructRector`

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

<br>

### MultiExceptionCatchRector

Changes multi catch of same exception to single one | separated.

- class: `Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector`

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

<br>

### PublicConstantVisibilityRector

Add explicit public constant visibility.

- class: `Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector`

```diff
 class SomeClass
 {
-    const HEY = 'you';
+    public const HEY = 'you';
 }
```

<br>

### RemoveExtraParametersRector

Remove extra parameters

- class: `Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector`

```diff
-strlen("asdf", 1);
+strlen("asdf");
```

<br>

### ReservedObjectRector

Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured

:wrench: **configure it!**

- class: `Rector\Php71\Rector\Name\ReservedObjectRector`

```php
use Rector\Php71\Rector\Name\ReservedObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReservedObjectRector::class)
        ->call('configure', [[
            ReservedObjectRector::RESERVED_KEYWORDS_TO_REPLACEMENTS => [
                'ReservedObject' => 'SmartObject',
                'Object' => 'AnotherSmartObject',
            ],
        ]]);
};
```

↓

```diff
-class Object
+class SmartObject
 {
 }
```

<br>

## Php72

### CreateFunctionToAnonymousFunctionRector

Use anonymous functions instead of deprecated `create_function()`

- class: `Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector`

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

<br>

### GetClassOnNullRector

Null is no more allowed in `get_class()`

- class: `Rector\Php72\Rector\FuncCall\GetClassOnNullRector`

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

<br>

### IsObjectOnIncompleteClassRector

Incomplete class returns inverted bool on `is_object()`

- class: `Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector`

```diff
 $incompleteObject = new __PHP_Incomplete_Class;
-$isObject = is_object($incompleteObject);
+$isObject = ! is_object($incompleteObject);
```

<br>

### ListEachRector

`each()` function is deprecated, use `key()` and `current()` instead

- class: `Rector\Php72\Rector\Assign\ListEachRector`

```diff
-list($key, $callback) = each($callbacks);
+$key = key($callbacks);
+$callback = current($callbacks);
+next($callbacks);
```

<br>

### ParseStrWithResultArgumentRector

Use `$result` argument in `parse_str()` function

- class: `Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector`

```diff
-parse_str($this->query);
-$data = get_defined_vars();
+parse_str($this->query, $result);
+$data = $result;
```

<br>

### ReplaceEachAssignmentWithKeyCurrentRector

Replace `each()` assign outside loop

- class: `Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector`

```diff
 $array = ['b' => 1, 'a' => 2];
-$eachedArray = each($array);
+$eachedArray[1] = current($array);
+$eachedArray['value'] = current($array);
+$eachedArray[0] = key($array);
+$eachedArray['key'] = key($array);
+next($array);
```

<br>

### StringifyDefineRector

Make first argument of `define()` string

- class: `Rector\Php72\Rector\FuncCall\StringifyDefineRector`

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

<br>

### StringsAssertNakedRector

String asserts must be passed directly to `assert()`

- class: `Rector\Php72\Rector\FuncCall\StringsAssertNakedRector`

```diff
 function nakedAssert()
 {
-    assert('true === true');
-    assert("true === true");
+    assert(true === true);
+    assert(true === true);
 }
```

<br>

### UnsetCastRector

Removes (unset) cast

- class: `Rector\Php72\Rector\Unset_\UnsetCastRector`

```diff
-$different = (unset) $value;
+$different = null;

-$value = (unset) $value;
+unset($value);
```

<br>

### WhileEachToForeachRector

`each()` function is deprecated, use `foreach()` instead.

- class: `Rector\Php72\Rector\While_\WhileEachToForeachRector`

```diff
-while (list($key, $callback) = each($callbacks)) {
+foreach ($callbacks as $key => $callback) {
     // ...
 }
```

<br>

```diff
-while (list($key) = each($callbacks)) {
+foreach (array_keys($callbacks) as $key) {
     // ...
 }
```

<br>

## Php73

### ArrayKeyFirstLastRector

Make use of `array_key_first()` and `array_key_last()`

- class: `Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector`

```diff
-reset($items);
-$firstKey = key($items);
+$firstKey = array_key_first($items);
```

<br>

```diff
-end($items);
-$lastKey = key($items);
+$lastKey = array_key_last($items);
```

<br>

### IsCountableRector

Changes `is_array` + Countable check to `is_countable`

- class: `Rector\Php73\Rector\BooleanOr\IsCountableRector`

```diff
-is_array($foo) || $foo instanceof Countable;
+is_countable($foo);
```

<br>

### JsonThrowOnErrorRector

Adds JSON_THROW_ON_ERROR to `json_encode()` and `json_decode()` to throw JsonException on error

- class: `Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector`

```diff
-json_encode($content);
-json_decode($json);
+json_encode($content, JSON_THROW_ON_ERROR);
+json_decode($json, null, null, JSON_THROW_ON_ERROR);
```

<br>

### RegexDashEscapeRector

Escape - in some cases

- class: `Rector\Php73\Rector\FuncCall\RegexDashEscapeRector`

```diff
-preg_match("#[\w-()]#", 'some text');
+preg_match("#[\w\-()]#", 'some text');
```

<br>

### SensitiveConstantNameRector

Changes case insensitive constants to sensitive ones.

- class: `Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector`

```diff
 define('FOO', 42, true);
 var_dump(FOO);
-var_dump(foo);
+var_dump(FOO);
```

<br>

### SensitiveDefineRector

Changes case insensitive constants to sensitive ones.

- class: `Rector\Php73\Rector\FuncCall\SensitiveDefineRector`

```diff
-define('FOO', 42, true);
+define('FOO', 42);
```

<br>

### SensitiveHereNowDocRector

Changes heredoc/nowdoc that contains closing word to safe wrapper name

- class: `Rector\Php73\Rector\String_\SensitiveHereNowDocRector`

```diff
-$value = <<<A
+$value = <<<A_WRAP
     A
-A
+A_WRAP
```

<br>

### SetCookieRector

Convert `setcookie` argument to PHP7.3 option array

- class: `Rector\Php73\Rector\FuncCall\SetCookieRector`

```diff
-setcookie('name', $value, 360);
+setcookie('name', $value, ['expires' => 360]);
```

<br>

```diff
-setcookie('name', $name, 0, '', '', true, true);
+setcookie('name', $name, ['expires' => 0, 'path' => '', 'domain' => '', 'secure' => true, 'httponly' => true]);
```

<br>

### StringifyStrNeedlesRector

Makes needles explicit strings

- class: `Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector`

```diff
 $needle = 5;
-$fivePosition = strpos('725', $needle);
+$fivePosition = strpos('725', (string) $needle);
```

<br>

## Php74

### AddLiteralSeparatorToNumberRector

Add "_" as thousands separator in numbers for higher or equals to limitValue config

:wrench: **configure it!**

- class: `Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector`

```php
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddLiteralSeparatorToNumberRector::class)
        ->call('configure', [[
            AddLiteralSeparatorToNumberRector::LIMIT_VALUE => 1000000,
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $int = 500000;
-        $float = 1000500.001;
+        $int = 500_000;
+        $float = 1_000_500.001;
     }
 }
```

<br>

### ArrayKeyExistsOnPropertyRector

Change `array_key_exists()` on property to `property_exists()`

- class: `Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector`

```diff
 class SomeClass
 {
      public $value;
 }
 $someClass = new SomeClass;

-array_key_exists('value', $someClass);
+property_exists($someClass, 'value');
```

<br>

### ArraySpreadInsteadOfArrayMergeRector

Change `array_merge()` to spread operator, except values with possible string `key` values

- class: `Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector`

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

<br>

### ChangeReflectionTypeToStringToGetNameRector

Change string calls on ReflectionType

- class: `Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector`

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

<br>

### ClosureToArrowFunctionRector

Change closure to arrow function

- class: `Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector`

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

<br>

### ExportToReflectionFunctionRector

Change `export()` to ReflectionFunction alternatives

- class: `Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector`

```diff
-$reflectionFunction = ReflectionFunction::export('foo');
-$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
+$reflectionFunction = new ReflectionFunction('foo');
+$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
```

<br>

### FilterVarToAddSlashesRector

Change `filter_var()` with slash escaping to `addslashes()`

- class: `Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector`

```diff
 $var= "Satya's here!";
-filter_var($var, FILTER_SANITIZE_MAGIC_QUOTES);
+addslashes($var);
```

<br>

### GetCalledClassToStaticClassRector

Change `get_called_class()` to static::class

- class: `Rector\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector`

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

<br>

### MbStrrposEncodingArgumentPositionRector

Change `mb_strrpos()` encoding argument position

- class: `Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector`

```diff
-mb_strrpos($text, "abc", "UTF-8");
+mb_strrpos($text, "abc", 0, "UTF-8");
```

<br>

### NullCoalescingOperatorRector

Use null coalescing operator ??=

- class: `Rector\Php74\Rector\Assign\NullCoalescingOperatorRector`

```diff
 $array = [];
-$array['user_id'] = $array['user_id'] ?? 'value';
+$array['user_id'] ??= 'value';
```

<br>

### RealToFloatTypeCastRector

Change deprecated (real) to (float)

- class: `Rector\Php74\Rector\Double\RealToFloatTypeCastRector`

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

<br>

### ReservedFnFunctionRector

Change `fn()` function name, since it will be reserved keyword

:wrench: **configure it!**

- class: `Rector\Php74\Rector\Function_\ReservedFnFunctionRector`

```php
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

<br>

### RestoreDefaultNullToNullableTypePropertyRector

Add null default to properties with PHP 7.4 property nullable type

- class: `Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector`

```diff
 class SomeClass
 {
-    public ?string $name;
+    public ?string $name = null;
 }
```

<br>

### TypedPropertyRector

Changes property `@var` annotations from annotation to type.

:wrench: **configure it!**

- class: `Rector\Php74\Rector\Property\TypedPropertyRector`

```php
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(TypedPropertyRector::class)
        ->call('configure', [[
            TypedPropertyRector::CLASS_LIKE_TYPE_ONLY => false,
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

<br>

## Php80

### AnnotationToAttributeRector

Change annotation to attribute

- class: `Rector\Php80\Rector\Class_\AnnotationToAttributeRector`

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

<br>

### ChangeSwitchToMatchRector

Change `switch()` to `match()`

- class: `Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector`

```diff
 class SomeClass
 {
     public function run()
     {
-        switch ($this->lexer->lookahead['type']) {
-            case Lexer::T_SELECT:
-                $statement = $this->SelectStatement();
-                break;
-
-            case Lexer::T_UPDATE:
-                $statement = $this->UpdateStatement();
-                break;
-
-            default:
-                $statement = $this->syntaxError('SELECT, UPDATE or DELETE');
-                break;
-        }
+        $statement = match ($this->lexer->lookahead['type']) {
+            Lexer::T_SELECT => $this->SelectStatement(),
+            Lexer::T_UPDATE => $this->UpdateStatement(),
+            default => $this->syntaxError('SELECT, UPDATE or DELETE'),
+        };
     }
 }
```

<br>

### ClassOnObjectRector

Change get_class($object) to faster `$object::class`

- class: `Rector\Php80\Rector\FuncCall\ClassOnObjectRector`

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

<br>

### ClassPropertyAssignToConstructorPromotionRector

Change simple property init and assign to constructor promotion

- class: `Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector`

```diff
 class SomeClass
 {
-    public float $someVariable;
-
-    public function __construct(float $someVariable = 0.0)
+    public function __construct(private float $someVariable = 0.0)
     {
-        $this->someVariable = $someVariable;
     }
 }
```

<br>

### FinalPrivateToPrivateVisibilityRector

Changes method visibility from final private to only private

- class: `Rector\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector`

```diff
 class SomeClass
 {
-    final private function getter() {
+    private function getter() {
         return $this;
     }
 }
```

<br>

### GetDebugTypeRector

Change ternary type resolve to `get_debug_type()`

- class: `Rector\Php80\Rector\Ternary\GetDebugTypeRector`

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

<br>

### NullsafeOperatorRector

Change if null check with nullsafe operator ?-> with full short circuiting

- class: `Rector\Php80\Rector\If_\NullsafeOperatorRector`

```diff
 class SomeClass
 {
     public function run($someObject)
     {
-        $someObject2 = $someObject->mayFail1();
-        if ($someObject2 === null) {
-            return null;
-        }
-
-        return $someObject2->mayFail2();
+        return $someObject->mayFail1()?->mayFail2();
     }
 }
```

<br>

### OptionalParametersAfterRequiredRector

Move required parameters after optional ones

- class: `Rector\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector`

```diff
 class SomeObject
 {
-    public function run($optional = 1, $required)
+    public function run($required, $optional = 1)
     {
     }
 }
```

<br>

### RemoveUnusedVariableInCatchRector

Remove unused variable in `catch()`

- class: `Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector`

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

<br>

### SetStateToStaticRector

Adds static visibility to `__set_state()` methods

- class: `Rector\Php80\Rector\ClassMethod\SetStateToStaticRector`

```diff
 class SomeClass
 {
-    public function __set_state($properties) {
+    public static function __set_state($properties) {

     }
 }
```

<br>

### StrContainsRector

Replace `strpos()` !== false and `strstr()`  with `str_contains()`

- class: `Rector\Php80\Rector\NotIdentical\StrContainsRector`

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

<br>

### StrEndsWithRector

Change helper functions to `str_ends_with()`

- class: `Rector\Php80\Rector\Identical\StrEndsWithRector`

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

<br>

### StrStartsWithRector

Change helper functions to `str_starts_with()`

- class: `Rector\Php80\Rector\Identical\StrStartsWithRector`

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

<br>

### StringableForToStringRector

Add `Stringable` interface to classes with `__toString()` method

- class: `Rector\Php80\Rector\Class_\StringableForToStringRector`

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

<br>

### TokenGetAllToObjectRector

Complete missing constructor dependency instance by type

- class: `Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector`

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

<br>

### UnionTypesRector

Change docs types to union types, where possible (properties are covered by TypedPropertiesRector)

- class: `Rector\Php80\Rector\FunctionLike\UnionTypesRector`

```diff
 class SomeClass
 {
-    /**
-     * @param array|int $number
-     * @return bool|float
-     */
-    public function go($number)
+    public function go(array|int $number): bool|float
     {
     }
 }
```

<br>

## PhpSpecToPHPUnit

### AddMockPropertiesRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector`

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

<br>

### MockVariableToPropertyFetchRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector`

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

<br>

### PhpSpecClassToPHPUnitClassRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector`

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

<br>

### PhpSpecMethodToPHPUnitMethodRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector`

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

<br>

### PhpSpecMocksToPHPUnitMocksRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector`

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

<br>

### PhpSpecPromisesToPHPUnitAssertRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector`

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

<br>

### RenameSpecFileToTestFileRector

Rename "*Spec.php" file to "*Test.php" file

- class: `Rector\PhpSpecToPHPUnit\Rector\FileNode\RenameSpecFileToTestFileRector`

```diff
-// tests/SomeSpec.php
+// tests/SomeTest.php
```

<br>

## Privatization

### ChangeGlobalVariablesToPropertiesRector

Change global `$variables` to private properties

- class: `Rector\Privatization\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector`

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

<br>

### ChangeLocalPropertyToVariableRector

Change local property used in single method to local variable

- class: `Rector\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector`

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

<br>

### ChangeReadOnlyPropertyWithDefaultValueToConstantRector

Change property with read only status with default value to constant

- class: `Rector\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector`

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

<br>

### ChangeReadOnlyVariableWithDefaultValueToConstantRector

Change variable with read only status with default value to constant

- class: `Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector`

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

<br>

### FinalizeClassesWithoutChildrenRector

Finalize every class that has no children

- class: `Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector`

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

<br>

### MakeOnlyUsedByChildrenProtectedRector

Make public class method protected, if only used by its children

- class: `Rector\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector`

```diff
 abstract class AbstractSomeClass
 {
-    public function run()
+    protected function run()
     {
     }
 }

 class ChildClass extends AbstractSomeClass
 {
     public function go()
     {
         $this->run();
     }
 }
```

<br>

### MakeUnusedClassesWithChildrenAbstractRector

Classes that have no children nor are used, should have abstract

- class: `Rector\Privatization\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector`

```diff
 class SomeClass extends PossibleAbstractClass
 {
 }

-class PossibleAbstractClass
+abstract class PossibleAbstractClass
 {
 }
```

<br>

### PrivatizeFinalClassMethodRector

Change protected class method to private if possible

- class: `Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector`

```diff
 final class SomeClass
 {
-    protected function someMethod()
+    private function someMethod()
     {
     }
 }
```

<br>

### PrivatizeFinalClassPropertyRector

Change property to private if possible

- class: `Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector`

```diff
 final class SomeClass
 {
-    protected $value;
+    private $value;
 }
```

<br>

### PrivatizeLocalClassConstantRector

Finalize every class constant that is used only locally

- class: `Rector\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector`

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

<br>

### PrivatizeLocalGetterToPropertyRector

Privatize getter of local property to property

- class: `Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector`

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

<br>

### PrivatizeLocalOnlyMethodRector

Privatize local-only use methods

- class: `Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector`

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

<br>

### PrivatizeLocalPropertyToPrivatePropertyRector

Privatize local-only property to private property

- class: `Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector`

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

<br>

### RepeatedLiteralToClassConstantRector

Replace repeated strings with constant

- class: `Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector`

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

<br>

### ReplaceStringWithClassConstantRector

Replace string values in specific method call by constant of provided class

:wrench: **configure it!**

- class: `Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector`

```php
use Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector;
use Rector\Privatization\ValueObject\ReplaceStringWithClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplaceStringWithClassConstantRector::class)
        ->call('configure', [[
            ReplaceStringWithClassConstantRector::REPLACE_STRING_WITH_CLASS_CONSTANT => ValueObjectInliner::inline([
                new ReplaceStringWithClassConstant('SomeClass', 'call', 'Placeholder', 0),
            ]),
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $this->call('name');
+        $this->call(Placeholder::NAME);
     }
 }
```

<br>

## RectorGenerator

### AddNewServiceToSymfonyPhpConfigRector

Adds a new `$services->set(...)` call to PHP Config

- class: `Rector\RectorGenerator\Rector\Closure\AddNewServiceToSymfonyPhpConfigRector`

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();
+    $services->set(AddNewServiceToSymfonyPhpConfigRector::class);
 };
```

<br>

## Removing

### ArgumentRemoverRector

Removes defined arguments in defined methods and their calls.

:wrench: **configure it!**

- class: `Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector`

```php
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            ArgumentRemoverRector::REMOVED_ARGUMENTS => ValueObjectInliner::inline([
                new ArgumentRemover('ExampleClass', 'someMethod', 0, 'true'),
            ]),
        ]]);
};
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(true);
+$someObject->someMethod();'
```

<br>

### RemoveFuncCallArgRector

Remove argument by position by function name

:wrench: **configure it!**

- class: `Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector`

```php
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveFuncCallArgRector::class)
        ->call('configure', [[
            RemoveFuncCallArgRector::REMOVED_FUNCTION_ARGUMENTS => ValueObjectInliner::inline([
                new RemoveFuncCallArg('remove_last_arg', 1),
            ]),
        ]]);
};
```

↓

```diff
-remove_last_arg(1, 2);
+remove_last_arg(1);
```

<br>

### RemoveFuncCallRector

Remove `ini_get` by configuration

:wrench: **configure it!**

- class: `Rector\Removing\Rector\FuncCall\RemoveFuncCallRector`

```php
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveFuncCallRector::class)
        ->call('configure', [[
            RemoveFuncCallRector::REMOVE_FUNC_CALLS => ValueObjectInliner::inline([
                new RemoveFuncCall('ini_get', [['y2k_compliance']]), ]
            ),
        ]]);
};
```

↓

```diff
-ini_get('y2k_compliance');
 ini_get('keep_me');
```

<br>

### RemoveInterfacesRector

Removes interfaces usage from class.

:wrench: **configure it!**

- class: `Rector\Removing\Rector\Class_\RemoveInterfacesRector`

```php
use Rector\Removing\Rector\Class_\RemoveInterfacesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveInterfacesRector::class)
        ->call('configure', [[
            RemoveInterfacesRector::INTERFACES_TO_REMOVE => ['SomeInterface'],
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

<br>

### RemoveParentRector

Removes extends class by name

:wrench: **configure it!**

- class: `Rector\Removing\Rector\Class_\RemoveParentRector`

```php
use Rector\Removing\Rector\Class_\RemoveParentRector;
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

<br>

### RemoveTraitRector

Remove specific traits from code

:wrench: **configure it!**

- class: `Rector\Removing\Rector\Class_\RemoveTraitRector`

```php
use Rector\Removing\Rector\Class_\RemoveTraitRector;
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

<br>

## RemovingStatic

### DesiredClassTypeToDynamicRector

Change full static service, to dynamic one

- class: `Rector\RemovingStatic\Rector\Class_\DesiredClassTypeToDynamicRector`

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

<br>

### DesiredPropertyClassMethodTypeToDynamicRector

Change defined static properties and methods to dynamic

- class: `Rector\RemovingStatic\Rector\Property\DesiredPropertyClassMethodTypeToDynamicRector`

```diff
 final class SomeClass
 {
-    public static $name;
+    public $name;

-    public static function go()
+    public function go()
     {
     }
 }
```

<br>

### DesiredStaticCallTypeToDynamicRector

Change defined static service to dynamic one

- class: `Rector\RemovingStatic\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector`

```diff
 final class SomeClass
 {
     public function run()
     {
-        SomeStaticMethod::someStatic();
+        $this->someStaticMethod::someStatic();
     }
 }
```

<br>

### DesiredStaticPropertyFetchTypeToDynamicRector

Change defined static service to dynamic one

- class: `Rector\RemovingStatic\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector`

```diff
 final class SomeClass
 {
     public function run()
     {
-        SomeStaticMethod::$someStatic;
+        $this->someStaticMethod::$someStatic;
     }
 }
```

<br>

### LocallyCalledStaticMethodToNonStaticRector

Change static method and local-only calls to non-static

- class: `Rector\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector`

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

<br>

### NewUniqueObjectToEntityFactoryRector

Convert new X to new factories

:wrench: **configure it!**

- class: `Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector`

```php
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

<br>

### PHPUnitStaticToKernelTestCaseGetRector

Convert static calls in PHPUnit test cases, to `get()` from the container of KernelTestCase

:wrench: **configure it!**

- class: `Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector`

```php
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
+        $this->entityFactory = $this->getService(EntityFactory::class);
+    }
+
     public function test()
     {
-        $product = EntityFactory::create('product');
+        $product = $this->entityFactory->create('product');
     }
 }
```

<br>

### PassFactoryToUniqueObjectRector

Convert new `X/Static::call()` to factories in entities, pass them via constructor to each other

:wrench: **configure it!**

- class: `Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector`

```php
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

<br>

### StaticTypeToSetterInjectionRector

Changes types to setter injection

:wrench: **configure it!**

- class: `Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector`

```php
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

<br>

## Renaming

### PseudoNamespaceToNamespaceRector

Replaces defined Pseudo_Namespaces by Namespace\Ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector`

```php
use Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PseudoNamespaceToNamespaceRector::class)
        ->call('configure', [[
            PseudoNamespaceToNamespaceRector::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES => ValueObjectInliner::inline([
                new PseudoNamespaceToNamespace('Some_', ['Some_Class_To_Keep']), ]
            ),
        ]]);
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

<br>

### RenameAnnotationRector

Turns defined annotations above properties and methods to their new values.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector`

```php
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameAnnotationRector::class)
        ->call('configure', [[
            RenameAnnotationRector::RENAMED_ANNOTATIONS_IN_TYPES => ValueObjectInliner::inline([
                new RenameAnnotation('PHPUnit\Framework\TestCase', 'test', 'scenario'),
            ]),
        ]]);
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

<br>

### RenameClassConstFetchRector

Replaces defined class constants in their calls.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector`

```php
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassConstFetchRector::class)
        ->call('configure', [[
            RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([
                new RenameClassConstFetch('SomeClass', 'OLD_CONSTANT', 'NEW_CONSTANT'),
                new RenameClassAndConstFetch('SomeClass', 'OTHER_OLD_CONSTANT', 'NEW_CONSTANT', 'DifferentClass'),
            ]),
        ]]);
};
```

↓

```diff
-$value = SomeClass::OLD_CONSTANT;
-$value = SomeClass::OTHER_OLD_CONSTANT;
+$value = SomeClass::NEW_CONSTANT;
+$value = DifferentClass::NEW_CONSTANT;
```

<br>

### RenameClassRector

Replaces defined classes by new ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\Name\RenameClassRector`

```php
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

<br>

### RenameConstantRector

Replace constant by new ones

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\ConstFetch\RenameConstantRector`

```php
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameConstantRector::class)
        ->call('configure', [[
            RenameConstantRector::OLD_TO_NEW_CONSTANTS => [
                'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
                'OLD_CONSTANT' => 'NEW_CONSTANT',
            ],
        ]]);
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

<br>

### RenameFunctionRector

Turns defined function call new one.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\FuncCall\RenameFunctionRector`

```php
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameFunctionRector::class)
        ->call('configure', [[
            RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
                'view' => 'Laravel\Templating\render',
            ],
        ]]);
};
```

↓

```diff
-view("...", []);
+Laravel\Templating\render("...", []);
```

<br>

### RenameMethodRector

Turns method names to new ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\MethodCall\RenameMethodRector`

```php
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('SomeExampleClass', 'oldMethod', 'newMethod'),
            ]),
        ]]);
};
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->oldMethod();
+$someObject->newMethod();
```

<br>

### RenameNamespaceRector

Replaces old namespace by new one.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\Namespace_\RenameNamespaceRector`

```php
use Rector\Renaming\Rector\Namespace_\RenameNamespaceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameNamespaceRector::class)
        ->call('configure', [[
            RenameNamespaceRector::OLD_TO_NEW_NAMESPACES => [
                'SomeOldNamespace' => 'SomeNewNamespace',
            ],
        ]]);
};
```

↓

```diff
-$someObject = new SomeOldNamespace\SomeClass;
+$someObject = new SomeNewNamespace\SomeClass;
```

<br>

### RenamePropertyRector

Replaces defined old properties by new ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector`

```php
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\RenameProperty;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::RENAMED_PROPERTIES => ValueObjectInliner::inline([
                new RenameProperty('SomeClass', 'someOldProperty', 'someNewProperty'),
            ]),
        ]]);
};
```

↓

```diff
-$someObject->someOldProperty;
+$someObject->someNewProperty;
```

<br>

### RenameStaticMethodRector

Turns method names to new ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector`

```php
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => ValueObjectInliner::inline([
                new RenameStaticMethod('SomeClass', 'oldMethod', 'AnotherExampleClass', 'newStaticMethod'),
            ]),
        ]]);
};
```

↓

```diff
-SomeClass::oldStaticMethod();
+AnotherExampleClass::newStaticMethod();
```

<br>

```php
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => ValueObjectInliner::inline([
                new RenameStaticMethod('SomeClass', 'oldMethod', 'SomeClass', 'newStaticMethod'),
            ]),
        ]]);
};
```

↓

```diff
-SomeClass::oldStaticMethod();
+SomeClass::newStaticMethod();
```

<br>

### RenameStringRector

Change string value

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\String_\RenameStringRector`

```php
use Rector\Renaming\Rector\String_\RenameStringRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameStringRector::class)
        ->call('configure', [[
            RenameStringRector::STRING_CHANGES => [
                'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
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
-        return 'ROLE_PREVIOUS_ADMIN';
+        return 'IS_IMPERSONATOR';
     }
 }
```

<br>

## Restoration

### CompleteImportForPartialAnnotationRector

In case you have accidentally removed use imports but code still contains partial use statements, this will save you

:wrench: **configure it!**

- class: `Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector`

```php
use Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector;
use Rector\Restoration\ValueObject\CompleteImportForPartialAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CompleteImportForPartialAnnotationRector::class)
        ->call('configure', [[
            CompleteImportForPartialAnnotationRector::USE_IMPORTS_TO_RESTORE => ValueObjectInliner::inline([
                new CompleteImportForPartialAnnotation('Doctrine\ORM\Mapping', 'ORM'),
            ]),
        ]]);
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

<br>

### CompleteMissingDependencyInNewRector

Complete missing constructor dependency instance by type

:wrench: **configure it!**

- class: `Rector\Restoration\Rector\New_\CompleteMissingDependencyInNewRector`

```php
use Rector\Restoration\Rector\New_\CompleteMissingDependencyInNewRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CompleteMissingDependencyInNewRector::class)
        ->call('configure', [[
            CompleteMissingDependencyInNewRector::CLASS_TO_INSTANTIATE_BY_TYPE => [
                'RandomDependency' => 'RandomDependency',
            ],
        ]]);
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

<br>

### InferParamFromClassMethodReturnRector

Change `@param` doc based on another method return type

:wrench: **configure it!**

- class: `Rector\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector`

```php
use Rector\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector;
use Rector\Restoration\ValueObject\InferParamFromClassMethodReturn;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(InferParamFromClassMethodReturnRector::class)
        ->call('configure', [[
            InferParamFromClassMethodReturnRector::PARAM_FROM_CLASS_METHOD_RETURNS => ValueObjectInliner::inline([
                new InferParamFromClassMethodReturn('SomeClass', 'process', 'getNodeTypes'),
            ]),
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function getNodeTypes(): array
     {
         return [String_::class];
     }

+    /**
+     * @param String_ $node
+     */
     public function process(Node $node)
     {
     }
 }
```

<br>

### MakeTypedPropertyNullableIfCheckedRector

Make typed property nullable if checked

- class: `Rector\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector`

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

<br>

### MissingClassConstantReferenceToStringRector

Convert missing class reference to string

- class: `Rector\Restoration\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector`

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

<br>

### RemoveFinalFromEntityRector

Remove final from Doctrine entities

- class: `Rector\Restoration\Rector\Class_\RemoveFinalFromEntityRector`

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

<br>

### RestoreFullyQualifiedNameRector

Restore accidentally shortened class names to its fully qualified form.

- class: `Rector\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector`

```diff
-use ShortClassOnly;
+use App\Whatever\ShortClassOnly;

 class AnotherClass
 {
 }
```

<br>

### UpdateFileNameByClassNameFileSystemRector

Rename file to respect class name

- class: `Rector\Restoration\Rector\ClassLike\UpdateFileNameByClassNameFileSystemRector`

```diff
-// app/SomeClass.php
+// app/AnotherClass.php
 class AnotherClass
 {
 }
```

<br>

## Sensio

### RemoveServiceFromSensioRouteRector

Remove service from Sensio `@Route`

- class: `Rector\Sensio\Rector\ClassMethod\RemoveServiceFromSensioRouteRector`

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

<br>

### ReplaceSensioRouteAnnotationWithSymfonyRector

Replace Sensio `@Route` annotation with Symfony one

- class: `Rector\Sensio\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector`

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

<br>

### TemplateAnnotationToThisRenderRector

Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony

- class: `Rector\Sensio\Rector\ClassMethod\TemplateAnnotationToThisRenderRector`

```diff
-/**
- * @Template()
- */
 public function indexAction()
 {
+    return $this->render('index.html.twig');
 }
```

<br>

## Symfony

### ActionSuffixRemoverRector

Removes Action suffixes from methods in Symfony Controllers

- class: `Rector\Symfony\Rector\ClassMethod\ActionSuffixRemoverRector`

```diff
 class SomeController
 {
-    public function indexAction()
+    public function index()
     {
     }
 }
```

<br>

### ChangeFileLoaderInExtensionAndKernelRector

Change XML loader to YAML in Bundle Extension

:wrench: **configure it!**

- class: `Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector`

```php
use Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeFileLoaderInExtensionAndKernelRector::class)
        ->call('configure', [[
            ChangeFileLoaderInExtensionAndKernelRector::FROM => 'xml',
            ChangeFileLoaderInExtensionAndKernelRector::TO => 'yaml',
        ]]);
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

<br>

### GetParameterToConstructorInjectionRector

Turns fetching of parameters via `getParameter()` in ContainerAware to constructor injection in Command and Controller in Symfony

- class: `Rector\Symfony\Rector\MethodCall\GetParameterToConstructorInjectionRector`

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

<br>

### GetToConstructorInjectionRector

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

:wrench: **configure it!**

- class: `Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector`

```php
use Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetToConstructorInjectionRector::class)
        ->call('configure', [[
            GetToConstructorInjectionRector::GET_METHOD_AWARE_TYPES => [
                'SymfonyControllerClassName',
                'GetTraitClassName',
            ],
        ]]);
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

<br>

### MakeCommandLazyRector

Make Symfony commands lazy

- class: `Rector\Symfony\Rector\Class_\MakeCommandLazyRector`

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

<br>

### NormalizeAutowireMethodNamingRector

Use autowire + class name suffix for method with `@required` annotation

- class: `Rector\Symfony\Rector\ClassMethod\NormalizeAutowireMethodNamingRector`

```diff
 class SomeClass
 {
     /** @required */
-    public function foo()
+    public function autowireSomeClass()
     {
     }
 }
```

<br>

### ResponseStatusCodeRector

Turns status code numbers to constants

- class: `Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector`

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

<br>

### SimpleFunctionAndFilterRector

Changes Twig_Function_Method to Twig_SimpleFunction calls in Twig_Extension.

- class: `Rector\Symfony\Rector\Return_\SimpleFunctionAndFilterRector`

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

<br>

## Symfony2

### AddFlashRector

Turns long flash adding to short helper method in Controller in Symfony

- class: `Rector\Symfony2\Rector\MethodCall\AddFlashRector`

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

<br>

### ParseFileRector

session > use_strict_mode is true by default and can be removed

- class: `Rector\Symfony2\Rector\StaticCall\ParseFileRector`

```diff
-session > use_strict_mode: true
+session:
```

<br>

### RedirectToRouteRector

Turns redirect to route to short helper method in Controller in Symfony

- class: `Rector\Symfony2\Rector\MethodCall\RedirectToRouteRector`

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

<br>

## Symfony3

### CascadeValidationFormBuilderRector

Change "cascade_validation" option to specific node attribute

- class: `Rector\Symfony3\Rector\MethodCall\CascadeValidationFormBuilderRector`

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

<br>

### ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector

Rename `type` option to `entry_type` in CollectionType

- class: `Rector\Symfony3\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector`

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

<br>

### ChangeStringCollectionOptionToConstantRector

Change type in CollectionType from alias string to class reference

- class: `Rector\Symfony3\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector`

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

<br>

### ConsoleExceptionToErrorEventConstantRector

Turns old event name with EXCEPTION to ERROR constant in Console in Symfony

- class: `Rector\Symfony3\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector`

```diff
-"console.exception"
+Symfony\Component\Console\ConsoleEvents::ERROR
```

<br>

```diff
-Symfony\Component\Console\ConsoleEvents::EXCEPTION
+Symfony\Component\Console\ConsoleEvents::ERROR
```

<br>

### FormTypeGetParentRector

Turns string Form Type references to their `CONSTANT` alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony

- class: `Rector\Symfony3\Rector\ClassMethod\FormTypeGetParentRector`

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

<br>

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

<br>

### FormTypeInstanceToClassConstRector

Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"

- class: `Rector\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector`

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

<br>

### GetRequestRector

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

- class: `Rector\Symfony3\Rector\ClassMethod\GetRequestRector`

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

<br>

### MergeMethodAnnotationToRouteAnnotationRector

Merge removed `@Method` annotation to `@Route` one

- class: `Rector\Symfony3\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector`

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

<br>

### OptionNameRector

Turns old option names to new ones in FormTypes in Form in Symfony

- class: `Rector\Symfony3\Rector\MethodCall\OptionNameRector`

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

<br>

### ReadOnlyOptionToAttributeRector

Change "read_only" option in form to attribute

- class: `Rector\Symfony3\Rector\MethodCall\ReadOnlyOptionToAttributeRector`

```diff
 use Symfony\Component\Form\FormBuilderInterface;

 function buildForm(FormBuilderInterface $builder, array $options)
 {
-    $builder->add('cuid', TextType::class, ['read_only' => true]);
+    $builder->add('cuid', TextType::class, ['attr' => ['read_only' => true]]);
 }
```

<br>

### RemoveDefaultGetBlockPrefixRector

Rename `getBlockPrefix()` if it returns the default value - class to underscore, e.g. UserFormType = user_form

- class: `Rector\Symfony3\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector`

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

<br>

### StringFormTypeToClassRector

Turns string Form Type references to their `CONSTANT` alternatives in FormTypes in Form in Symfony. To enable custom types, add `link` to your container XML `dump` in "$parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, ...);"

- class: `Rector\Symfony3\Rector\MethodCall\StringFormTypeToClassRector`

```diff
 $formBuilder = new Symfony\Component\Form\FormBuilder;
-$formBuilder->add('name', 'form.type.text');
+$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

<br>

## Symfony4

### ConsoleExecuteReturnIntRector

Returns int from Command::execute command

- class: `Rector\Symfony4\Rector\ClassMethod\ConsoleExecuteReturnIntRector`

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

<br>

### ConstraintUrlOptionRector

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

- class: `Rector\Symfony4\Rector\ConstFetch\ConstraintUrlOptionRector`

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

<br>

### ContainerBuilderCompileEnvArgumentRector

Turns old default value to parameter in `ContainerBuilder->build()` method in DI in Symfony

- class: `Rector\Symfony4\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector`

```diff
 use Symfony\Component\DependencyInjection\ContainerBuilder;

 $containerBuilder = new ContainerBuilder();
-$containerBuilder->compile();
+$containerBuilder->compile(true);
```

<br>

### ContainerGetToConstructorInjectionRector

Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony

:wrench: **configure it!**

- class: `Rector\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector`

```php
use Rector\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ContainerGetToConstructorInjectionRector::class)
        ->call('configure', [[
            ContainerGetToConstructorInjectionRector::CONTAINER_AWARE_PARENT_TYPES => [
                'ContainerAwareParentClassName',
                'ContainerAwareParentCommandClassName',
                'ThisClassCallsMethodInConstructorClassName',
            ],
        ]]);
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

<br>

### FormIsValidRector

Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony

- class: `Rector\Symfony4\Rector\MethodCall\FormIsValidRector`

```diff
-if ($form->isValid()) {
+if ($form->isSubmitted() && $form->isValid()) {
 }
```

<br>

### MakeDispatchFirstArgumentEventRector

Make event object a first argument of `dispatch()` method, event name as second

- class: `Rector\Symfony4\Rector\MethodCall\MakeDispatchFirstArgumentEventRector`

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

<br>

### ProcessBuilderGetProcessRector

Removes `$processBuilder->getProcess()` calls to `$processBuilder` in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.

- class: `Rector\Symfony4\Rector\MethodCall\ProcessBuilderGetProcessRector`

```diff
 $processBuilder = new Symfony\Component\Process\ProcessBuilder;
-$process = $processBuilder->getProcess();
-$commamdLine = $processBuilder->getProcess()->getCommandLine();
+$process = $processBuilder;
+$commamdLine = $processBuilder->getCommandLine();
```

<br>

### ProcessBuilderInstanceRector

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

- class: `Rector\Symfony4\Rector\StaticCall\ProcessBuilderInstanceRector`

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

<br>

### RootNodeTreeBuilderRector

Changes  Process string argument to an array

- class: `Rector\Symfony4\Rector\New_\RootNodeTreeBuilderRector`

```diff
 use Symfony\Component\Config\Definition\Builder\TreeBuilder;

-$treeBuilder = new TreeBuilder();
-$rootNode = $treeBuilder->root('acme_root');
+$treeBuilder = new TreeBuilder('acme_root');
+$rootNode = $treeBuilder->getRootNode();
 $rootNode->someCall();
```

<br>

### SimplifyWebTestCaseAssertionsRector

Simplify use of assertions in WebTestCase

- class: `Rector\Symfony4\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector`

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

<br>

### StringToArrayArgumentProcessRector

Changes Process string argument to an array

- class: `Rector\Symfony4\Rector\New_\StringToArrayArgumentProcessRector`

```diff
 use Symfony\Component\Process\Process;
-$process = new Process('ls -l');
+$process = new Process(['ls', '-l']);
```

<br>

### VarDumperTestTraitMethodArgsRector

Adds a new `$filter` argument in `VarDumperTestTrait->assertDumpEquals()` and `VarDumperTestTrait->assertDumpMatchesFormat()` in Validator in Symfony.

- class: `Rector\Symfony4\Rector\MethodCall\VarDumperTestTraitMethodArgsRector`

```diff
-$varDumperTestTrait->assertDumpEquals($dump, $data, $message = "");
+$varDumperTestTrait->assertDumpEquals($dump, $data, $filter = 0, $message = "");
```

<br>

```diff
-$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $message = "");
+$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $filter = 0, $message = "");
```

<br>

## Symfony5

### BinaryFileResponseCreateToNewInstanceRector

Change deprecated `BinaryFileResponse::create()` to use `__construct()` instead

- class: `Rector\Symfony5\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector`

```diff
 use Symfony\Component\HttpFoundation;

 class SomeClass
 {
     public function run()
     {
-        $binaryFile = BinaryFileResponse::create();
+        $binaryFile = new BinaryFileResponse(null);
     }
 }
```

<br>

### DefinitionAliasSetPrivateToSetPublicRector

Migrates from deprecated `Definition/Alias->setPrivate()` to `Definition/Alias->setPublic()`

- class: `Rector\Symfony5\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector`

```diff
 use Symfony\Component\DependencyInjection\Alias;
 use Symfony\Component\DependencyInjection\Definition;

 class SomeClass
 {
     public function run()
     {
         $definition = new Definition('Example\Foo');
-        $definition->setPrivate(false);
+        $definition->setPublic(true);

         $alias = new Alias('Example\Foo');
-        $alias->setPrivate(false);
+        $alias->setPublic(true);
     }
 }
```

<br>

### FormBuilderSetDataMapperRector

Migrates from deprecated Form Builder->setDataMapper(new `PropertyPathMapper())` to Builder->setDataMapper(new DataMapper(new `PropertyPathAccessor()))`

- class: `Rector\Symfony5\Rector\MethodCall\FormBuilderSetDataMapperRector`

```diff
 use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
 use Symfony\Component\Form\FormConfigBuilderInterface;

 class SomeClass
 {
     public function run(FormConfigBuilderInterface $builder)
     {
-        $builder->setDataMapper(new PropertyPathMapper());
+        $builder->setDataMapper(new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper(new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor()));
     }
 }
```

<br>

### LogoutHandlerToLogoutEventSubscriberRector

Change logout handler to an event listener that listens to LogoutEvent

- class: `Rector\Symfony5\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector`

```diff
-use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
-use Symfony\Component\HttpFoundation\Request;
-use Symfony\Component\HttpFoundation\Response;
-use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
+use Symfony\Component\EventDispatcher\EventSubscriberInterface;
+use Symfony\Component\Security\Http\Event\LogoutEvent;

-final class SomeLogoutHandler implements LogoutHandlerInterface
+final class SomeLogoutHandler implements EventSubscriberInterface
 {
-    public function logout(Request $request, Response $response, TokenInterface $token)
+    public function onLogout(LogoutEvent $logoutEvent): void
     {
+        $request = $logoutEvent->getRequest();
+        $response = $logoutEvent->getResponse();
+        $token = $logoutEvent->getToken();
+    }
+
+    /**
+     * @return array<string, string[]>
+     */
+    public static function getSubscribedEvents(): array
+    {
+        return [
+            LogoutEvent::class => ['onLogout'],
+        ];
     }
 }
```

<br>

### LogoutSuccessHandlerToLogoutEventSubscriberRector

Change logout success handler to an event listener that listens to LogoutEvent

- class: `Rector\Symfony5\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector`

```diff
-use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
-use Symfony\Component\HttpFoundation\Request;
-use Symfony\Component\HttpFoundation\Response;
-use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
+use Symfony\Component\EventDispatcher\EventSubscriberInterface;
+use Symfony\Component\Security\Http\Event\LogoutEvent;

-final class SomeLogoutHandler implements LogoutSuccessHandlerInterface
+final class SomeLogoutHandler implements EventSubscriberInterface
 {
     /**
       * @var HttpUtils
       */
     private $httpUtils;

-    public function __construct(HttpUtils $httpUtils)
+    public function onLogout(LogoutEvent $logoutEvent): void
     {
-        $this->httpUtils = $httpUtils;
+        if ($logoutEvent->getResponse() !== null) {
+            return;
+        }
+
+        $response = $this->httpUtils->createRedirectResponse($logoutEvent->getRequest(), 'some_url');
+        $logoutEvent->setResponse($response);
     }

-    public function onLogoutSuccess(Request $request)
+    /**
+     * @return array<string, mixed>
+     */
+    public static function getSubscribedEvents(): array
     {
-        $response = $this->httpUtils->createRedirectResponse($request, 'some_url');
-        return $response;
+        return [
+            LogoutEvent::class => [['onLogout', 64]],
+        ];
     }
 }
```

<br>

### PropertyAccessorCreationBooleanToFlagsRector

Changes first argument of `PropertyAccessor::__construct()` to flags from boolean

- class: `Rector\Symfony5\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector`

```diff
 class SomeClass
 {
     public function run()
     {
-        $propertyAccessor = new PropertyAccessor(true);
+        $propertyAccessor = new PropertyAccessor(PropertyAccessor::MAGIC_CALL | PropertyAccessor::MAGIC_GET | PropertyAccessor::MAGIC_SET);
     }
 }
```

<br>

### PropertyPathMapperToDataMapperRector

Migrate from PropertyPathMapper to DataMapper and PropertyPathAccessor

- class: `Rector\Symfony5\Rector\New_\PropertyPathMapperToDataMapperRector`

```diff
 use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

 class SomeClass
 {
     public function run()
     {
-        return new PropertyPathMapper();
+        return new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper(new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor());
     }
 }
```

<br>

### ReflectionExtractorEnableMagicCallExtractorRector

Migrates from deprecated enable_magic_call_extraction context option in ReflectionExtractor

- class: `Rector\Symfony5\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector`

```diff
 use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

 class SomeClass
 {
     public function run()
     {
         $reflectionExtractor = new ReflectionExtractor();
         $readInfo = $reflectionExtractor->getReadInfo(Dummy::class, 'bar', [
-            'enable_magic_call_extraction' => true,
+            'enable_magic_methods_extraction' => ReflectionExtractor::MAGIC_CALL | ReflectionExtractor::MAGIC_GET | ReflectionExtractor::MAGIC_SET,
         ]);
     }
 }
```

<br>

### ValidatorBuilderEnableAnnotationMappingRector

Migrates from deprecated ValidatorBuilder->enableAnnotationMapping($reader) to ValidatorBuilder->enableAnnotationMapping(true)->setDoctrineAnnotationReader($reader)

- class: `Rector\Symfony5\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector`

```diff
 use Doctrine\Common\Annotations\Reader;
 use Symfony\Component\Validator\ValidatorBuilder;

 class SomeClass
 {
     public function run(ValidatorBuilder $builder, Reader $reader)
     {
-        $builder->enableAnnotationMapping($reader);
+        $builder->enableAnnotationMapping(true)->setDoctrineAnnotationReader($reader);
     }
 }
```

<br>

## SymfonyCodeQuality

### EventListenerToEventSubscriberRector

Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file

- class: `Rector\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector`

```diff
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

<br>

### ExtractAttributeRouteNameConstantsRector

`Extract` #[Route] attribute name argument from string to constant

- class: `Rector\SymfonyCodeQuality\Rector\Attribute\ExtractAttributeRouteNameConstantsRector`

```diff
 use Symfony\Component\Routing\Annotation\Route;

 class SomeClass
 {
-    #[Route(path: "path", name: "/name")]
+    #[Route(path: "path", name: RouteName::NAME)]
     public function run()
     {
     }
 }
```

Extra file:

```php
final class RouteName
{
    /**
     * @var string
     */
    public NAME = 'name';
}
```

<br>

## SymfonyPhpConfig

### AutoInPhpSymfonyConfigRector

Make sure there is `public()`, `autowire()`, `autoconfigure()` calls on `defaults()` in Symfony configs

- class: `Rector\SymfonyPhpConfig\Rector\MethodCall\AutoInPhpSymfonyConfigRector`

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();

     $services->defaults()
-        ->autowire();
+        ->autowire()
+        ->public()
+        ->autoconfigure();
 };
```

<br>

## Transform

### ArgumentFuncCallToMethodCallRector

Move help facade-like function calls to constructor injection

:wrench: **configure it!**

- class: `Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector`

```php
use Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentFuncCallToMethodCallRector::class)
        ->call('configure', [[
            ArgumentFuncCallToMethodCallRector::FUNCTIONS_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new ArgumentFuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', null, 'make'),
            ]),
        ]]);
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

<br>

### CallableInMethodCallToVariableRector

Change a callable in method call to standalone variable assign

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector`

```php
use Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector;
use Rector\Transform\ValueObject\CallableInMethodCallToVariable;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CallableInMethodCallToVariableRector::class)
        ->call('configure', [[
            CallableInMethodCallToVariableRector::CALLABLE_IN_METHOD_CALL_TO_VARIABLE => ValueObjectInliner::inline([
                new CallableInMethodCallToVariable('Nette\Caching\Cache', 'save', 1),
            ]),
        ]]);
};
```

↓

```diff
 final class SomeClass
 {
     public function run()
     {
         /** @var \Nette\Caching\Cache $cache */
-        $cache->save($key, function () use ($container) {
-            return 100;
-        });
+        $result = 100;
+        $cache->save($key, $result);
     }
 }
```

<br>

### ClassConstFetchToValueRector

Replaces constant by value

:wrench: **configure it!**

- class: `Rector\Transform\Rector\ClassConstFetch\ClassConstFetchToValueRector`

```php
use Rector\Transform\Rector\ClassConstFetch\ClassConstFetchToValueRector;
use Rector\Transform\ValueObject\ClassConstFetchToValue;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ClassConstFetchToValueRector::class)
        ->call('configure', [[
            ClassConstFetchToValueRector::CLASS_CONST_FETCHES_TO_VALUES => ValueObjectInliner::inline([
                new ClassConstFetchToValue('Nette\Configurator', 'DEVELOPMENT', 'development'),
                new ClassConstFetchToValue('Nette\Configurator', 'PRODUCTION', 'production'),
            ]),
        ]]);
};
```

↓

```diff
-$value === Nette\Configurator::DEVELOPMENT
+$value === "development"
```

<br>

### CommunityTestCaseRector

Change Rector test case to Community version

- class: `Rector\Transform\Rector\Class_\CommunityTestCaseRector`

```diff
-use Rector\Testing\PHPUnit\AbstractRectorTestCase;
+use Rector\Testing\PHPUnit\AbstractCommunityRectorTestCase;

-final class SomeClassTest extends AbstractRectorTestCase
+final class SomeClassTest extends AbstractCommunityRectorTestCase
 {
-    public function getRectorClass(): string
+    public function provideConfigFilePath(): string
     {
-        return SomeRector::class;
+        return __DIR__ . '/config/configured_rule.php';
     }
 }
```

<br>

### DimFetchAssignToMethodCallRector

Change magic array access add to `$list[],` to explicit `$list->addMethod(...)`

:wrench: **configure it!**

- class: `Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector`

```php
use Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DimFetchAssignToMethodCallRector::class)
        ->call('configure', [[
            DimFetchAssignToMethodCallRector::DIM_FETCH_ASSIGN_TO_METHOD_CALL => ValueObjectInliner::inline([
                new DimFetchAssignToMethodCall('Nette\Application\Routers\RouteList', 'Nette\Application\Routers\Route', 'addRoute'),
            ]),
        ]]);
};
```

↓

```diff
-use Nette\Application\Routers\Route;
 use Nette\Application\Routers\RouteList;

 class RouterFactory
 {
     public static function createRouter()
     {
         $routeList = new RouteList();
-        $routeList[] = new Route('...');
+        $routeList->addRoute('...');
     }
 }
```

<br>

### FuncCallToMethodCallRector

Turns defined function calls to local method calls.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector`

```php
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToMethodCallRector::class)
        ->call('configure', [[
            FuncCallToMethodCallRector::FUNC_CALL_TO_CLASS_METHOD_CALL => ValueObjectInliner::inline([
                new FuncCallToMethodCall('view', 'Namespaced\SomeRenderer', 'render'),
            ]),
        ]]);
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

<br>

### FuncCallToNewRector

Change configured function calls to new Instance

:wrench: **configure it!**

- class: `Rector\Transform\Rector\FuncCall\FuncCallToNewRector`

```php
use Rector\Transform\Rector\FuncCall\FuncCallToNewRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToNewRector::class)
        ->call('configure', [[
            FuncCallToNewRector::FUNCTIONS_TO_NEWS => [
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

<br>

### FuncCallToStaticCallRector

Turns defined function call to static method call.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector`

```php
use Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToStaticCallRector::class)
        ->call('configure', [[
            FuncCallToStaticCallRector::FUNC_CALLS_TO_STATIC_CALLS => ValueObjectInliner::inline([
                new FuncCallToStaticCall('view', 'SomeStaticClass', 'render'),
            ]),
        ]]);
};
```

↓

```diff
-view("...", []);
+SomeClass::render("...", []);
```

<br>

### GetAndSetToMethodCallRector

Turns defined `__get`/`__set` to specific method calls.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\Assign\GetAndSetToMethodCallRector`

```php
use Rector\Transform\Rector\Assign\GetAndSetToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetAndSetToMethodCallRector::class)
        ->call('configure', [[
            GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => [
                'SomeContainer' => [
                    'set' => 'addService',
                ],
            ],
        ]]);
};
```

↓

```diff
 $container = new SomeContainer;
-$container->someService = $someService;
+$container->setService("someService", $someService);
```

<br>

```php
use Rector\Transform\Rector\Assign\GetAndSetToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetAndSetToMethodCallRector::class)
        ->call('configure', [[
            GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => [
                'SomeContainer' => [
                    'get' => 'getService',
                ],
            ],
        ]]);
};
```

↓

```diff
 $container = new SomeContainer;
-$someService = $container->someService;
+$someService = $container->getService("someService");
```

<br>

### MethodCallToAnotherMethodCallWithArgumentsRector

Turns old method call with specific types to new one with arguments

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector`

```php
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToAnotherMethodCallWithArgumentsRector::class)
        ->call('configure', [[
            MethodCallToAnotherMethodCallWithArgumentsRector::METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS => ValueObjectInliner::inline([
                new MethodCallRenameWithArrayKey('Nette\DI\ServiceDefinition', 'setInject', 'addTag', 'inject'),
            ]),
        ]]);
};
```

↓

```diff
 $serviceDefinition = new Nette\DI\ServiceDefinition;
-$serviceDefinition->setInject();
+$serviceDefinition->addTag('inject');
```

<br>

### MethodCallToPropertyFetchRector

Turns method call `"$this->something()"` to property fetch "$this->something"

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector`

```php
use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToPropertyFetchRector::class)
        ->call('configure', [[
            MethodCallToPropertyFetchRector::METHOD_CALL_TO_PROPERTY_FETCHES => [
                'someMethod' => 'someProperty',
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
-        $this->someMethod();
+        $this->someProperty;
     }
 }
```

<br>

### MethodCallToReturnRector

Wrap method call to return

:wrench: **configure it!**

- class: `Rector\Transform\Rector\Expression\MethodCallToReturnRector`

```php
use Rector\Transform\Rector\Expression\MethodCallToReturnRector;
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

<br>

### MethodCallToStaticCallRector

Change method call to desired static call

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector`

```php
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToStaticCallRector::class)
        ->call('configure', [[
            MethodCallToStaticCallRector::METHOD_CALLS_TO_STATIC_CALLS => ValueObjectInliner::inline([
                new MethodCallToStaticCall('AnotherDependency', 'process', 'StaticCaller', 'anotherMethod'),
            ]),
        ]]);
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

<br>

### NewArgToMethodCallRector

Change new with specific argument to method call

:wrench: **configure it!**

- class: `Rector\Transform\Rector\New_\NewArgToMethodCallRector`

```php
use Rector\Transform\Rector\New_\NewArgToMethodCallRector;
use Rector\Transform\ValueObject\NewArgToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewArgToMethodCallRector::class)
        ->call('configure', [[
            NewArgToMethodCallRector::NEW_ARGS_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new NewArgToMethodCall('Dotenv', true, 'usePutenv'),
            ]),
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $dotenv = new Dotenv(true);
+        $dotenv = new Dotenv();
+        $dotenv->usePutenv();
     }
 }
```

<br>

### NewToConstructorInjectionRector

Change defined new type to constructor injection

:wrench: **configure it!**

- class: `Rector\Transform\Rector\New_\NewToConstructorInjectionRector`

```php
use Rector\Transform\Rector\New_\NewToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewToConstructorInjectionRector::class)
        ->call('configure', [[
            NewToConstructorInjectionRector::TYPES_TO_CONSTRUCTOR_INJECTION => ['Validator'],
        ]]);
};
```

↓

```diff
 class SomeClass
 {
+    /**
+     * @var Validator
+     */
+    private $validator;
+
+    public function __construct(Validator $validator)
+    {
+        $this->validator = $validator;
+    }
+
     public function run()
     {
-        $validator = new Validator();
-        $validator->validate(1000);
+        $this->validator->validate(1000);
     }
 }
```

<br>

### NewToMethodCallRector

Replaces creating object instances with "new" keyword with factory method.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\New_\NewToMethodCallRector`

```php
use Rector\Transform\Rector\New_\NewToMethodCallRector;
use Rector\Transform\ValueObject\NewToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewToMethodCallRector::class)
        ->call('configure', [[
            NewToMethodCallRector::NEWS_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new NewToMethodCall('MyClass', 'MyClassFactory', 'create'),
            ]),
        ]]);
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

<br>

### NewToStaticCallRector

Change new Object to static call

:wrench: **configure it!**

- class: `Rector\Transform\Rector\New_\NewToStaticCallRector`

```php
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\NewToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewToStaticCallRector::class)
        ->call('configure', [[
            NewToStaticCallRector::TYPE_TO_STATIC_CALLS => ValueObjectInliner::inline([
                new NewToStaticCall('Cookie', 'Cookie', 'create'),
            ]),
        ]]);
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

<br>

### ParentClassToTraitsRector

Replaces parent class to specific traits

:wrench: **configure it!**

- class: `Rector\Transform\Rector\Class_\ParentClassToTraitsRector`

```php
use Rector\Transform\Rector\Class_\ParentClassToTraitsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ParentClassToTraitsRector::class)
        ->call('configure', [[
            ParentClassToTraitsRector::PARENT_CLASS_TO_TRAITS => [
                'Nette\Object' => ['Nette\SmartObject'],
            ],
        ]]);
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

<br>

### PropertyAssignToMethodCallRector

Turns property assign of specific type and property name to method call

:wrench: **configure it!**

- class: `Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector`

```php
use Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector;
use Rector\Transform\ValueObject\PropertyAssignToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyAssignToMethodCallRector::class)
        ->call('configure', [[
            PropertyAssignToMethodCallRector::PROPERTY_ASSIGNS_TO_METHODS_CALLS => ValueObjectInliner::inline([
                new PropertyAssignToMethodCall('SomeClass', 'oldProperty', 'newMethodCall'),
            ]),
        ]]);
};
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->oldProperty = false;
+$someObject->newMethodCall(false);
```

<br>

### PropertyFetchToMethodCallRector

Replaces properties assign calls be defined methods.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector`

```php
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyFetchToMethodCallRector::class)
        ->call('configure', [[
            PropertyFetchToMethodCallRector::PROPERTIES_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new PropertyFetchToMethodCall('SomeObject', 'property', 'getProperty', [], 'setProperty'),
            ]),
        ]]);
};
```

↓

```diff
-$result = $object->property;
-$object->property = $value;
+$result = $object->getProperty();
+$object->setProperty($value);
```

<br>

```php
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyFetchToMethodCallRector::class)
        ->call('configure', [[
            PropertyFetchToMethodCallRector::PROPERTIES_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new PropertyFetchToMethodCall('SomeObject', 'property', 'getConfig', ['someArg'], null),
            ]),
        ]]);
};
```

↓

```diff
-$result = $object->property;
+$result = $object->getProperty('someArg');
```

<br>

### ReplaceParentCallByPropertyCallRector

Changes method calls in child of specific types to defined property method call

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector`

```php
use Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplaceParentCallByPropertyCallRector::class)
        ->call('configure', [[
            ReplaceParentCallByPropertyCallRector::PARENT_CALLS_TO_PROPERTIES => ValueObjectInliner::inline([
                new ReplaceParentCallByPropertyCall('SomeTypeToReplace', 'someMethodCall', 'someProperty'),
            ]),
        ]]);
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

<br>

### ServiceGetterToConstructorInjectionRector

Get service call to constructor injection

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector`

```php
use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->call('configure', [[
            ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => ValueObjectInliner::inline([
                new ServiceGetterToConstructorInjection('FirstService', 'getAnotherService', 'AnotherService'),
            ]),
        ]]);
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

<br>

### StaticCallToFuncCallRector

Turns static call to function call.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector`

```php
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticCallToFuncCallRector::class)
        ->call('configure', [[
            StaticCallToFuncCallRector::STATIC_CALLS_TO_FUNCTIONS => ValueObjectInliner::inline([
                new StaticCallToFuncCall('OldClass', 'oldMethod', 'new_function'),
            ]),
        ]]);
};
```

↓

```diff
-OldClass::oldMethod("args");
+new_function("args");
```

<br>

### StaticCallToMethodCallRector

Change static call to service method via constructor injection

:wrench: **configure it!**

- class: `Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector`

```php
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticCallToMethodCallRector::class)
        ->call('configure', [[
            StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new StaticCallToMethodCall(
                    'Nette\Utils\FileSystem',
                    'write',
                    'Symplify\SmartFileSystem\SmartFileSystem',
                    'dumpFile'
                ),
            ]),
        ]]);
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

<br>

### StaticCallToNewRector

Change static call to new instance

:wrench: **configure it!**

- class: `Rector\Transform\Rector\StaticCall\StaticCallToNewRector`

```php
use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\ValueObject\StaticCallToNew;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticCallToNewRector::class)
        ->call('configure', [[
            StaticCallToNewRector::STATIC_CALLS_TO_NEWS => ValueObjectInliner::inline([
                new StaticCallToNew('JsonResponse', 'create'),
            ]),
        ]]);
};
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $dotenv = JsonResponse::create(true);
+        $dotenv = new JsonResponse();
     }
 }
```

<br>

### StringToClassConstantRector

Changes strings to specific constants

:wrench: **configure it!**

- class: `Rector\Transform\Rector\String_\StringToClassConstantRector`

```php
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringToClassConstantRector::class)
        ->call('configure', [[
            StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => ValueObjectInliner::inline([
                new StringToClassConstant('compiler.post_dump', 'Yet\AnotherClass', 'CONSTANT'),
            ]),
        ]]);
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

<br>

### ToStringToMethodCallRector

Turns defined code uses of `"__toString()"` method  to specific method calls.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\String_\ToStringToMethodCallRector`

```php
use Rector\Transform\Rector\String_\ToStringToMethodCallRector;
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

<br>

### UnsetAndIssetToMethodCallRector

Turns defined `__isset`/`__unset` calls to specific method calls.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector`

```php
use Rector\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\Transform\ValueObject\UnsetAndIssetToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(UnsetAndIssetToMethodCallRector::class)
        ->call('configure', [[
            UnsetAndIssetToMethodCallRector::ISSET_UNSET_TO_METHOD_CALL => ValueObjectInliner::inline([
                new UnsetAndIssetToMethodCall('SomeContainer', 'hasService', 'removeService'),
            ]),
        ]]);
};
```

↓

```diff
 $container = new SomeContainer;
-isset($container["someKey"]);
+$container->hasService("someKey");
```

<br>

```php
use Rector\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\Transform\ValueObject\UnsetAndIssetToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(UnsetAndIssetToMethodCallRector::class)
        ->call('configure', [[
            UnsetAndIssetToMethodCallRector::ISSET_UNSET_TO_METHOD_CALL => ValueObjectInliner::inline([
                new UnsetAndIssetToMethodCall('SomeContainer', 'hasService', 'removeService'),
            ]),
        ]]);
};
```

↓

```diff
 $container = new SomeContainer;
-unset($container["someKey"]);
+$container->removeService("someKey");
```

<br>

### VariableMethodCallToServiceCallRector

Replace variable method call to a service one

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\VariableMethodCallToServiceCallRector`

```php
use Rector\Transform\Rector\MethodCall\VariableMethodCallToServiceCallRector;
use Rector\Transform\ValueObject\VariableMethodCallToServiceCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(VariableMethodCallToServiceCallRector::class)
        ->call('configure', [[
            VariableMethodCallToServiceCallRector::VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS => ValueObjectInliner::inline([
                new VariableMethodCallToServiceCall(
                    'PhpParser\Node',
                    'getAttribute',
                    'php_doc_info',
                    'Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory',
                    'createFromNodeOrEmpty'
                ),
            ]),
        ]]);
};
```

↓

```diff
+use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
 use PhpParser\Node;

 class SomeClass
 {
+    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
+    {
+        $this->phpDocInfoFactory = $phpDocInfoFactory;
+    }
     public function run(Node $node)
     {
-        $phpDocInfo = $node->getAttribute('php_doc_info');
+        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
     }
 }
```

<br>

## TypeDeclaration

### AddArrayParamDocTypeRector

Adds `@param` annotation to array parameters inferred from the rest of the code

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector`

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

<br>

### AddArrayReturnDocTypeRector

Adds `@return` annotation to array parameters inferred from the rest of the code

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector`

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

<br>

### AddClosureReturnTypeRector

Add known return type to functions

- class: `Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector`

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

<br>

### AddMethodCallBasedParamTypeRector

Change param type of passed `getId()` to UuidInterface type declaration

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedParamTypeRector`

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

<br>

### AddParamTypeDeclarationRector

Add param types where needed

:wrench: **configure it!**

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector`

```php
use PHPStan\Type\StringType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
                new AddParamTypeDeclaration('SomeClass', 'process', 0, new StringType()),
            ]),
        ]]);
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

<br>

### AddReturnTypeDeclarationRector

Changes defined return typehint of method and class.

:wrench: **configure it!**

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector`

```php
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => ValueObjectInliner::inline([
                new AddReturnTypeDeclaration('SomeClass', 'getData', new ArrayType(new MixedType(false, null), new MixedType(
                    false,
                    null
                ))),
            ]),
        ]]);
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

<br>

### CompleteVarDocTypePropertyRector

Complete property `@var` annotations or correct the old ones

- class: `Rector\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector`

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

<br>

### FormerNullableArgumentToScalarTypedRector

Change null in argument, that is now not nullable anymore

- class: `Rector\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector`

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

<br>

### ParamTypeDeclarationRector

Change `@param` types to type declarations if not a BC-break

- class: `Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector`

```diff
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

-    /**
-     * @param int $number
-     */
-    public function change($number)
+    public function change(int $number)
     {
     }
 }
```

<br>

### PropertyTypeDeclarationRector

Add `@var` to properties that are missing it

- class: `Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector`

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

<br>

### ReturnTypeDeclarationRector

Change `@return` types and type from static analysis to type declarations if not a BC-break

- class: `Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector`

```diff
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

<br>

### ReturnTypeFromStrictTypedPropertyRector

Add return method return type based on strict typed property

- class: `Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector`

```diff
 final class SomeClass
 {
     private int $age = 100;

-    public function getAge()
+    public function getAge(): int
     {
         return $this->age;
     }
 }
```

<br>

### TypedPropertyFromStrictConstructorRector

Add typed properties based only on strict constructor types

- class: `Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector`

```diff
 class SomeObject
 {
-    private $name;
+    private string $name;

     public function __construct(string $name)
     {
         $this->name = $name;
     }
 }
```

<br>

## Visibility

### ChangeConstantVisibilityRector

Change visibility of constant from parent class.

:wrench: **configure it!**

- class: `Rector\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector`

```php
use Rector\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector;
use Rector\Visibility\ValueObject\ChangeConstantVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeConstantVisibilityRector::class)
        ->call('configure', [[
            ChangeConstantVisibilityRector::CLASS_CONSTANT_VISIBILITY_CHANGES => ValueObjectInliner::inline([
                new ChangeConstantVisibility('ParentObject', 'SOME_CONSTANT', 2),
            ]),
        ]]);
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

<br>

### ChangeMethodVisibilityRector

Change visibility of method from parent class.

:wrench: **configure it!**

- class: `Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector`

```php
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => ValueObjectInliner::inline([
                new ChangeMethodVisibility('FrameworkClass', 'someMethod', 2),
            ]),
        ]]);
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

<br>

### ChangePropertyVisibilityRector

Change visibility of property from parent class.

:wrench: **configure it!**

- class: `Rector\Visibility\Rector\Property\ChangePropertyVisibilityRector`

```php
use Rector\Visibility\Rector\Property\ChangePropertyVisibilityRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangePropertyVisibilityRector::class)
        ->call('configure', [[
            ChangePropertyVisibilityRector::PROPERTY_TO_VISIBILITY_BY_CLASS => [
                'FrameworkClass' => [
                    'someProperty' => 2,
                ],
            ],
        ]]);
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

<br>
