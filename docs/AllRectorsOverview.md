# All 312 Rectors Overview

- [Projects](#projects)
- [General](#general)

## Projects

- [CakePHP](#cakephp)
- [Celebrity](#celebrity)
- [CodeQuality](#codequality)
- [CodingStyle](#codingstyle)
- [DeadCode](#deadcode)
- [Doctrine](#doctrine)
- [DomainDrivenDesign](#domaindrivendesign)
- [ElasticSearchDSL](#elasticsearchdsl)
- [Guzzle](#guzzle)
- [Laravel](#laravel)
- [Legacy](#legacy)
- [MysqlToMysqli](#mysqltomysqli)
- [Nette](#nette)
- [NetteTesterToPHPUnit](#nettetestertophpunit)
- [NetteToSymfony](#nettetosymfony)
- [PHPStan](#phpstan)
- [PHPUnit](#phpunit)
- [Php](#php)
- [PhpParser](#phpparser)
- [PhpSpecToPHPUnit](#phpspectophpunit)
- [RemovingStatic](#removingstatic)
- [SOLID](#solid)
- [Sensio](#sensio)
- [Shopware](#shopware)
- [Silverstripe](#silverstripe)
- [Sylius](#sylius)
- [Symfony](#symfony)
- [SymfonyPHPUnit](#symfonyphpunit)
- [Twig](#twig)
- [TypeDeclaration](#typedeclaration)

## CakePHP

### `ChangeSnakedFixtureNameToCamelRector`

- class: `Rector\CakePHP\Rector\Name\ChangeSnakedFixtureNameToCamelRector`

Changes $fixtues style from snake_case to CamelCase.

```diff
 class SomeTest
 {
     protected $fixtures = [
-        'app.posts',
-        'app.users',
-        'some_plugin.posts/special_posts',
+        'app.Posts',
+        'app.Users',
+        'some_plugin.Posts/SpeectialPosts',
     ];
```

<br>

### `ModalToGetSetRector`

- class: `Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector`

Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.

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

## Celebrity

### `CommonNotEqualRector`

- class: `Rector\Celebrity\Rector\NotEqual\CommonNotEqualRector`

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

<br>

### `LogicalToBooleanRector`

- class: `Rector\Celebrity\Rector\BooleanOp\LogicalToBooleanRector`

Change OR, AND to ||, && with more common understanding

```diff
-if ($f = false or true) {
+if (($f = false) || true) {
     return $f;
 }
```

<br>

### `SetTypeToCastRector`

- class: `Rector\Celebrity\Rector\FuncCall\SetTypeToCastRector`

Changes settype() to (type) where possible

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

## CodeQuality

### `AndAssignsToSeparateLinesRector`

- class: `Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector`

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

<br>

### `BooleanNotIdenticalToNotIdenticalRector`

- class: `Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector`

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

<br>

### `CallableThisArrayToAnonymousFunctionRector`

- class: `Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector`

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

<br>

### `CombinedAssignRector`

- class: `Rector\CodeQuality\Rector\Assign\CombinedAssignRector`

Simplify $value = $value + 5; assignments to shorter ones

```diff
-$value = $value + 5;
+$value += 5;
```

<br>

### `CompactToVariablesRector`

- class: `Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector`

Change compact() call to own array

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

### `CompleteDynamicPropertiesRector`

- class: `Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector`

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

<br>

### `ConsecutiveNullCompareReturnsToNullCoalesceQueueRector`

- class: `Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector`

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

<br>

### `ExplicitBoolCompareRector`

- class: `Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector`

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

<br>

### `ForToForeachRector`

- class: `Rector\CodeQuality\Rector\For_\ForToForeachRector`

Change for() to foreach() where useful

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

<br>

### `ForeachToInArrayRector`

- class: `Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector`

Simplify `foreach` loops into `in_array` when possible

```diff
-foreach ($items as $item) {
-    if ($item === "something") {
-        return true;
-    }
-}
-
-return false;
+in_array("something", $items, true);
```

<br>

### `GetClassToInstanceOfRector`

- class: `Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector`

Changes comparison with get_class to instanceof

```diff
-if (EventsListener::class === get_class($event->job)) { }
+if ($event->job instanceof EventsListener) { }
```

<br>

### `InArrayAndArrayKeysToArrayKeyExistsRector`

- class: `Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector`

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
```

<br>

### `JoinStringConcatRector`

- class: `Rector\CodeQuality\Rector\Concat\JoinStringConcatRector`

Joins concat of 2 strings

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

### `SimplifyArraySearchRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector`

Simplify array_search to in_array

```diff
-array_search("searching", $array) !== false;
+in_array("searching", $array, true);
```

```diff
-array_search("searching", $array) != false;
+in_array("searching", $array);
```

<br>

### `SimplifyBoolIdenticalTrueRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector`

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

<br>

### `SimplifyConditionsRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector`

Simplify conditions

```diff
-if (! ($foo !== 'bar')) {...
+if ($foo === 'bar') {...
```

<br>

### `SimplifyDeMorganBinaryRector`

- class: `Rector\CodeQuality\Rector\BinaryOp\SimplifyDeMorganBinaryRector`

Simplify negated conditions with de Morgan theorem

```diff
 <?php

 $a = 5;
 $b = 10;
-$result = !($a > 20 || $b <= 50);
+$result = $a <= 20 && $b > 50;
```

<br>

### `SimplifyDuplicatedTernaryRector`

- class: `Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector`

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

<br>

### `SimplifyEmptyArrayCheckRector`

- class: `Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector`

Simplify `is_array` and `empty` functions combination into a simple identical check for an empty array

```diff
-is_array($values) && empty($values)
+$values === []
```

<br>

### `SimplifyForeachToArrayFilterRector`

- class: `Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector`

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

<br>

### `SimplifyForeachToCoalescingRector`

- class: `Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector`

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

<br>

### `SimplifyFuncGetArgsCountRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector`

Simplify count of func_get_args() to fun_num_args()

```diff
-count(func_get_args());
+func_num_args();
```

<br>

### `SimplifyIfElseToTernaryRector`

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector`

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

<br>

### `SimplifyIfIssetToNullCoalescingRector`

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector`

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

<br>

### `SimplifyIfNotNullReturnRector`

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector`

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

<br>

### `SimplifyIfReturnBoolRector`

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector`

Shortens if return false/true to direct return

```diff
-if (strpos($docToken->getContent(), "\n") === false) {
-    return true;
-}
-
-return false;
+return strpos($docToken->getContent(), "\n") === false;
```

<br>

### `SimplifyInArrayValuesRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector`

Removes unneeded array_values() in in_array() call

```diff
-in_array("key", array_values($array), true);
+in_array("key", $array, true);
```

<br>

### `SimplifyRegexPatternRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector`

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

<br>

### `SimplifyStrposLowerRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector`

Simplify strpos(strtolower(), "...") calls

```diff
-strpos(strtolower($var), "...")"
+stripos($var, "...")"
```

<br>

### `SimplifyTautologyTernaryRector`

- class: `Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector`

Simplify tautology ternary to value

```diff
-$value = ($fullyQualifiedTypeHint !== $typeHint) ? $fullyQualifiedTypeHint : $typeHint;
+$value = $fullyQualifiedTypeHint;
```

<br>

### `SimplifyUselessVariableRector`

- class: `Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector`

Removes useless variable assigns

```diff
 function () {
-    $a = true;
-    return $a;
+    return true;
 };
```

<br>

### `SingleInArrayToCompareRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector`

Changes in_array() with single element to ===

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

### `TernaryToElvisRector`

- class: `Rector\CodeQuality\Rector\Ternary\TernaryToElvisRector`

Use ?: instead of ?, where useful

```diff
 function elvis()
 {
-    $value = $a ? $a : false;
+    $value = $a ?: false;
 }
```

<br>

### `UnnecessaryTernaryExpressionRector`

- class: `Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector`

Remove unnecessary ternary expressions.

```diff
-$foo === $bar ? true : false;
+$foo === $bar;
```

<br>

### `UseIdenticalOverEqualWithSameTypeRector`

- class: `Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector`

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

<br>

## CodingStyle

### `ArrayPropertyDefaultValueRector`

- class: `Rector\CodingStyle\Rector\Property\ArrayPropertyDefaultValueRector`

Array property should have default value, to prevent undefined array issues

```diff
 class SomeClass
 {
     /**
      * @var int[]
      */
-    private $items;
+    private $items = [];

     public function run()
     {
         foreach ($items as $item) {
         }
     }
 }
```

<br>

### `BinarySwitchToIfElseRector`

- class: `Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector`

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

<br>

### `CatchExceptionNameMatchingTypeRector`

- class: `Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector`

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

<br>

### `ConsistentImplodeRector`

- class: `Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector`

Changes various implode forms to consistent one

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

### `ConsistentPregDelimiterRector`

- class: `Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector`

Replace PREG delimiter with configured one

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

### `FollowRequireByDirRector`

- class: `Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector`

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

<br>

### `IdenticalFalseToBooleanNotRector`

- class: `Rector\CodingStyle\Rector\Identical\IdenticalFalseToBooleanNotRector`

Changes === false to negate !

```diff
-if ($something === false) {}
+if (! $something) {}
```

<br>

### `ImportFullyQualifiedNamesRector`

- class: `Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector`

Import fully qualified names to use statements

```diff
+use SomeAnother\AnotherClass;
+
 class SomeClass
 {
     public function create()
     {
-          return SomeAnother\AnotherClass;
+          return AnotherClass;
     }
 }
```

<br>

### `NullableCompareToNullRector`

- class: `Rector\CodingStyle\Rector\If_\NullableCompareToNullRector`

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

<br>

### `RemoveUnusedAliasRector`

- class: `Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector`

Removes unused use aliases

```diff
-use Symfony\Kernel as BaseKernel;
+use Symfony\Kernel;

-class SomeClass extends BaseKernel
+class SomeClass extends Kernel
 {
 }
```

<br>

### `ReturnArrayClassMethodToYieldRector`

- class: `Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector`

Turns yield return to array return in specific type and method

```yaml
services:
    Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector:
        EventSubscriberInterface:
            - getSubscribedEvents
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

### `SimpleArrayCallableToStringRector`

- class: `Rector\CodingStyle\Rector\FuncCall\SimpleArrayCallableToStringRector`

Changes redundant anonymous bool functions to simple calls

```diff
-$paths = array_filter($paths, function ($path): bool {
-    return is_dir($path);
-});
+array_filter($paths, "is_dir");
```

<br>

### `SplitDoubleAssignRector`

- class: `Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector`

Split multiple inline assigns to each own lines default value, to prevent undefined array issues

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

### `SplitGroupedConstantsAndPropertiesRector`

- class: `Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector`

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

<br>

### `SplitStringClassConstantToClassConstFetchRector`

- class: `Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector`

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

<br>

### `SymplifyQuoteEscapeRector`

- class: `Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector`

Prefer quote that not inside the string

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

### `VarConstantCommentRector`

- class: `Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector`

Constant should have a @var comment with type

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

### `YieldClassMethodToArrayClassMethodRector`

- class: `Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector`

Turns yield return to array return in specific type and method

```yaml
services:
    Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector:
        EventSubscriberInterface:
            - getSubscribedEvents
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

## DeadCode

### `RemoveAndTrueRector`

- class: `Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector`

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

<br>

### `RemoveCodeAfterReturnRector`

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector`

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

<br>

### `RemoveConcatAutocastRector`

- class: `Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector`

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

<br>

### `RemoveDeadConstructorRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector`

Remove empty constructor

```diff
 class SomeClass
 {
-    public function __construct()
-    {
-    }
 }
```

<br>

### `RemoveDeadIfForeachForRector`

- class: `Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector`

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

<br>

### `RemoveDeadReturnRector`

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector`

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

<br>

### `RemoveDeadStmtRector`

- class: `Rector\DeadCode\Rector\Stmt\RemoveDeadStmtRector`

Removes dead code statements

```diff
-$value = 5;
-$value;
+$value = 5;
```

<br>

### `RemoveDeadZeroAndOneOperationRector`

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

### `RemoveDefaultArgumentValueRector`

- class: `Rector\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector`

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

<br>

### `RemoveDelegatingParentCallRector`

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

### `RemoveDoubleAssignRector`

- class: `Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector`

Simplify useless double assigns

```diff
-$value = 1;
 $value = 1;
```

<br>

### `RemoveDuplicatedArrayKeyRector`

- class: `Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector`

Remove duplicated key in defined arrays.

```diff
 $item = [
-    1 => 'A',
     1 => 'B'
 ];
```

<br>

### `RemoveDuplicatedInstanceOfRector`

- class: `Rector\DeadCode\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector`

```diff
 class SomeClass
 {
     public function run($value)
     {
-        $isIt = $value instanceof A || $value instanceof A;
-        $isIt = $value instanceof A && $value instanceof A;
+        $isIt = $value instanceof A;
+        $isIt = $value instanceof A;
     }
 }
```

<br>

### `RemoveEmptyClassMethodRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector`

Remove empty method calls not required by parents

```diff
 class OrphanClass
 {
-    public function __construct()
-    {
-    }
 }
```

<br>

### `RemoveOverriddenValuesRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveOverriddenValuesRector`

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

<br>

### `RemoveParentCallWithoutParentRector`

- class: `Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector`

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

<br>

### `RemoveUnusedForeachKeyRector`

- class: `Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector`

Remove unused key in foreach

```diff
 $items = [];
-foreach ($items as $key => $value) {
+foreach ($items as $value) {
     $result = $value;
 }
```

<br>

### `RemoveUnusedParameterRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector`

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

<br>

### `RemoveUnusedPrivateConstantRector`

- class: `Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector`

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

<br>

### `RemoveUnusedPrivateMethodRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector`

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

<br>

### `RemoveUnusedPrivatePropertyRector`

- class: `Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector`

Remove unused private properties

```diff
 class SomeClass
 {
-    private $property;
 }
```

<br>

### `RemoveZeroAndOneBinaryRector`

- class: `Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector`

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

### `SimplifyMirrorAssignRector`

- class: `Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector`

Removes unneeded $a = $a assigns

```diff
-$a = $a;
```

<br>

## Doctrine

### `AliasToClassRector`

- class: `Rector\Doctrine\Rector\AliasToClassRector`

Replaces doctrine alias with class.

```diff
 $entityManager = new Doctrine\ORM\EntityManager();
-$entityManager->getRepository("AppBundle:Post");
+$entityManager->getRepository(\App\Entity\Post::class);
```

<br>

### `ManagerRegistryGetManagerToEntityManagerRector`

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

## DomainDrivenDesign

### `ObjectToScalarDocBlockRector`

- class: `Rector\DomainDrivenDesign\Rector\ObjectToScalar\ObjectToScalarDocBlockRector`

Turns defined value object to simple types in doc blocks

```yaml
services:
    Rector\DomainDrivenDesign\Rector\ObjectToScalar\ObjectToScalarDocBlockRector:
        $valueObjectsToSimpleTypes:
            ValueObject: string
```

↓

```diff
 /**
- * @var ValueObject|null
+ * @var string|null
  */
 private $name;

-/** @var ValueObject|null */
+/** @var string|null */
 $name;
```

<br>

### `ObjectToScalarRector`

- class: `Rector\DomainDrivenDesign\Rector\ObjectToScalar\ObjectToScalarRector`

Remove values objects and use directly the value.

```yaml
services:
    Rector\DomainDrivenDesign\Rector\ObjectToScalar\ObjectToScalarRector:
        $valueObjectsToSimpleTypes:
            ValueObject: string
```

↓

```diff
-$name = new ValueObject("name");
+$name = "name";

-function someFunction(ValueObject $name): ?ValueObject {
+function someFunction(string $name): ?string {
 }
```

<br>

## ElasticSearchDSL

### `MigrateFilterToQueryRector`

- class: `Rector\ElasticSearchDSL\Rector\MethodCall\MigrateFilterToQueryRector`

Migrates addFilter to addQuery

```diff
 class SomeClass
 {
     public function run()
     {
         $search = new \ONGR\ElasticsearchDSL\Search();

-        $search->addFilter(
-            new \ONGR\ElasticsearchDSL\Query\TermsQuery('categoryIds', [1, 2])
+        $search->addQuery(
+            new \ONGR\ElasticsearchDSL\Query\TermsQuery('categoryIds', [1, 2]),
+            \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::FILTER
         );
     }
 }
```

<br>

## Guzzle

### `MessageAsArrayRector`

- class: `Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector`

Changes getMessage(..., true) to getMessageAsArray()

```diff
 /** @var GuzzleHttp\Message\MessageInterface */
-$value = $message->getMessage('key', true);
+$value = $message->getMessageAsArray('key');
```

<br>

## Laravel

### `FacadeStaticCallToConstructorInjectionRector`

- class: `Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector`

Move Illuminate\Support\Facades\* static calls to constructor injection

```diff
 use Illuminate\Support\Facades\Response;

 class ExampleController extends Controller
 {
+    /**
+     * @var \Illuminate\Contracts\Routing\ResponseFactory
+     */
+    private $responseFactory;
+
+    public function __construct(\Illuminate\Contracts\Routing\ResponseFactory $responseFactory)
+    {
+        $this->responseFactory = $responseFactory;
+    }
+
     public function store()
     {
-        return Response::view('example', ['new_example' => 123]);
+        return $this->responseFactory->view('example', ['new_example' => 123]);
     }
 }
```

<br>

### `HelperFunctionToConstructorInjectionRector`

- class: `Rector\Laravel\Rector\FuncCall\HelperFunctionToConstructorInjectionRector`

Move help facade-like function calls to constructor injection

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

### `MinutesToSecondsInCacheRector`

- class: `Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector`

Change minutes argument to seconds in Illuminate\Contracts\Cache\Store and Illuminate\Support\Facades\Cache

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

### `Redirect301ToPermanentRedirectRector`

- class: `Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector`

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

<br>

### `RequestStaticValidateToInjectRector`

- class: `Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector`

Change static validate() method to $request->validate()

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

### `ChangeSingletonToServiceRector`

- class: `Rector\Legacy\Rector\ClassMethod\ChangeSingletonToServiceRector`

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

<br>

## MysqlToMysqli

### `MysqlAssignToMysqliRector`

- class: `Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector`

Converts more complex mysql functions to mysqli

```diff
-$data = mysql_db_name($result, $row);
+mysqli_data_seek($result, $row);
+$fetch = mysql_fetch_row($result);
+$data = $fetch[0];
```

<br>

### `MysqlFuncCallToMysqliRector`

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector`

Converts more complex mysql functions to mysqli

```diff
-mysql_drop_db($database);
+mysqli_query('DROP DATABASE ' . $database);
```

<br>

### `MysqlPConnectToMysqliConnectRector`

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector`

Replace mysql_pconnect() with mysqli_connect() with host p: prefix

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

## Nette

### `EndsWithFunctionToNetteUtilsStringsRector`

- class: `Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector`

Use Nette\Utils\Strings over bare string-functions

```diff
 class SomeClass
 {
     public function end($needle)
     {
         $content = 'Hi, my name is Tom';

-        $yes = substr($content, -strlen($needle)) === $needle;
-        $no = $needle !== substr($content, -strlen($needle));
+        $yes = \Nette\Utils\Strings::endsWith($content, $needle);
+        $no = !\Nette\Utils\Strings::endsWith($content, $needle);
     }
 }
```

<br>

### `PregFunctionToNetteUtilsStringsRector`

- class: `Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector`

Use Nette\Utils\Strings over bare preg_* functions

```diff
 class SomeClass
 {
     public function run()
     {
         $content = 'Hi my name is Tom';
-        preg_match('#Hi#', $content);
+        \Nette\Utils\Strings::match($content, '#Hi#');
     }
 }
```

<br>

### `StartsWithFunctionToNetteUtilsStringsRector`

- class: `Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector`

Use Nette\Utils\Strings over bare string-functions

```diff
 class SomeClass
 {
     public function start($needle)
     {
         $content = 'Hi, my name is Tom';

-        $yes = substr($content, 0, strlen($needle)) === $needle;
-        $no = $needle !== substr($content, 0, strlen($needle));
+        $yes = \Nette\Utils\Strings::startwith($content, $needle);
+        $no = !\Nette\Utils\Strings::startwith($content, $needle);
     }
 }
```

<br>

### `StrposToStringsContainsRector`

- class: `Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector`

Use Nette\Utils\Strings over bare string-functions

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

### `SubstrStrlenFunctionToNetteUtilsStringsRector`

- class: `Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector`

Use Nette\Utils\Strings over bare string-functions

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

## NetteTesterToPHPUnit

### `NetteAssertToPHPUnitAssertRector`

- class: `Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector`

Migrate Nette/Assert calls to PHPUnit

```diff
 use Tester\Assert;

 function someStaticFunctions()
 {
-    Assert::true(10 == 5);
+    \PHPUnit\Framework\Assert::assertTrue(10 == 5);
 }
```

<br>

### `NetteTesterClassToPHPUnitClassRector`

- class: `Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector`

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

<br>

### `RenameTesterTestToPHPUnitToTestFileRector`

- class: `Rector\NetteTesterToPHPUnit\Rector\RenameTesterTestToPHPUnitToTestFileRector`

Rename "*.phpt" file to "*Test.php" file

<br>

## NetteToSymfony

### `FromHttpRequestGetHeaderToHeadersGetRector`

- class: `Rector\NetteToSymfony\Rector\MethodCall\FromHttpRequestGetHeaderToHeadersGetRector`

Changes getHeader() to $request->headers->get()

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

### `FromRequestGetParameterToAttributesGetRector`

- class: `Rector\NetteToSymfony\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector`

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

<br>

### `NetteControlToSymfonyControllerRector`

- class: `Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector`

Migrate Nette Component to Symfony Controller

```diff
 use Nette\Application\UI\Control;

-class SomeControl extends Control
+class SomeController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 {
-    public function render()
-    {
-        $this->template->param = 'some value';
-        $this->template->render(__DIR__ . '/poll.latte');
-    }
+     public function some()
+     {
+         $this->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
+     }
 }
```

<br>

### `NetteFormToSymfonyFormRector`

- class: `Rector\NetteToSymfony\Rector\Class_\NetteFormToSymfonyFormRector`

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

<br>

### `RenameEventNamesInEventSubscriberRector`

- class: `Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector`

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

<br>

### `RouterListToControllerAnnotationsRector`

- class: `Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector`

Change new Route() from RouteFactory to @Route annotation above controller method

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

 final class SomePresenter
 {
+    /**
+     * @Symfony\Component\Routing\Annotation\Route(path="some-path")
+     */
     public function run()
     {
     }
 }
```

<br>

### `WrapTransParameterNameRector`

- class: `Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector`

Adds %% to placeholder name of trans() method if missing

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

## PHPStan

### `PHPStormVarAnnotationRector`

- class: `Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector`

Change various @var annotation formats to one PHPStorm understands

```diff
-$config = 5;
-/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+$config = 5;
```

<br>

### `RecastingRemovalRector`

- class: `Rector\PHPStan\Rector\Cast\RecastingRemovalRector`

Removes recasting of the same type

```diff
 $string = '';
-$string = (string) $string;
+$string = $string;

 $array = [];
-$array = (array) $array;
+$array = $array;
```

<br>

## PHPUnit

### `AssertCompareToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertCompareToSpecificMethodRector`

Turns vague php-only method in PHPUnit TestCase to more specific

```diff
-$this->assertSame(10, count($anything), "message");
+$this->assertCount(10, $anything, "message");
```

```diff
-$this->assertSame($value, {function}($anything), "message");
+$this->assert{function}($value, $anything, "message\");
```

```diff
-$this->assertEquals($value, {function}($anything), "message");
+$this->assert{function}($value, $anything, "message\");
```

```diff
-$this->assertNotSame($value, {function}($anything), "message");
+$this->assertNot{function}($value, $anything, "message")
```

```diff
-$this->assertNotEquals($value, {function}($anything), "message");
+$this->assertNot{function}($value, $anything, "message")
```

<br>

### `AssertComparisonToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector`

Turns comparison operations to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo === $bar, "message");
+$this->assertSame($bar, $foo, "message");
```

```diff
-$this->assertFalse($foo >= $bar, "message");
+$this->assertLessThanOrEqual($bar, $foo, "message");
```

<br>

### `AssertEqualsParameterToSpecificMethodsTypeRector`

- class: `Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector`

Change assertEquals()/assertNotEquals() method parameters to new specific alternatives

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

### `AssertFalseStrposToContainsRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertFalseStrposToContainsRector`

Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertFalse(strpos($anything, "foo"), "message");
+$this->assertNotContains("foo", $anything, "message");
```

```diff
-$this->assertNotFalse(stripos($anything, "foo"), "message");
+$this->assertContains("foo", $anything, "message");
```

<br>

### `AssertInstanceOfComparisonRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector`

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo instanceof Foo, "message");
+$this->assertInstanceOf("Foo", $foo, "message");
```

```diff
-$this->assertFalse($foo instanceof Foo, "message");
+$this->assertNotInstanceOf("Foo", $foo, "message");
```

<br>

### `AssertIssetToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertIssetToSpecificMethodRector`

Turns isset comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(isset($anything->foo));
+$this->assertFalse(isset($anything["foo"]), "message");
```

```diff
-$this->assertObjectHasAttribute("foo", $anything);
+$this->assertArrayNotHasKey("foo", $anything, "message");
```

<br>

### `AssertNotOperatorRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertNotOperatorRector`

Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(!$foo, "message");
+$this->assertFalse($foo, "message");
```

```diff
-$this->assertFalse(!$foo, "message");
+$this->assertTrue($foo, "message");
```

<br>

### `AssertPropertyExistsRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertPropertyExistsRector`

Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(property_exists(new Class, "property"), "message");
+$this->assertClassHasAttribute("property", "Class", "message");
```

```diff
-$this->assertFalse(property_exists(new Class, "property"), "message");
+$this->assertClassNotHasAttribute("property", "Class", "message");
```

<br>

### `AssertRegExpRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertRegExpRector`

Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(1, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertRegExp("/^Message for ".*"\.$/", $string, $message);
```

```diff
-$this->assertEquals(false, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertNotRegExp("/^Message for ".*"\.$/", $string, $message);
```

<br>

### `AssertSameBoolNullToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertSameBoolNullToSpecificMethodRector`

Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(null, $anything);
+$this->assertNull($anything);
```

```diff
-$this->assertNotSame(false, $anything);
+$this->assertNotFalse($anything);
```

<br>

### `AssertTrueFalseInternalTypeToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseInternalTypeToSpecificMethodRector`

Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(is_{internal_type}($anything), "message");
+$this->assertInternalType({internal_type}, $anything, "message");
```

```diff
-$this->assertFalse(is_{internal_type}($anything), "message");
+$this->assertNotInternalType({internal_type}, $anything, "message");
```

<br>

### `AssertTrueFalseToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector`

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

<br>

### `DelegateExceptionArgumentsRector`

- class: `Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector`

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

```diff
-$this->setExpectedException(Exception::class, "Message", "CODE");
+$this->setExpectedException(Exception::class);
+$this->expectExceptionMessage("Message");
+$this->expectExceptionCode("CODE");
```

<br>

### `ExceptionAnnotationRector`

- class: `Rector\PHPUnit\Rector\ExceptionAnnotationRector`

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

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

### `GetMockRector`

- class: `Rector\PHPUnit\Rector\GetMockRector`

Turns getMock*() methods to createMock()

```diff
-$this->getMock("Class");
+$this->createMock("Class");
```

```diff
-$this->getMockWithoutInvokingTheOriginalConstructor("Class");
+$this->createMock("Class");
```

<br>

### `RemoveExpectAnyFromMockRector`

- class: `Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector`

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

<br>

### `ReplaceAssertArraySubsetRector`

- class: `Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetRector`

Replace deprecated "assertArraySubset()" method with alternative methods

```diff
 class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $checkedArray = [];

-        $this->assertArraySubset([
-           'cache_directory' => 'new_value',
-        ], $checkedArray);
+        $this->assertArrayHasKey('cache_directory', $checkedArray);
+        $this->assertSame('new_value', $checkedArray['cache_directory']);
     }
 }
```

<br>

### `SimplifyForeachInstanceOfRector`

- class: `Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector`

Simplify unnecessary foreach check of instances

```diff
-foreach ($foos as $foo) {
-    $this->assertInstanceOf(\SplFileInfo::class, $foo);
-}
+$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
```

<br>

### `SpecificAssertContainsRector`

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector`

Change assertContains()/assertNotContains() method to new string and iterable alternatives

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

<br>

### `SpecificAssertInternalTypeRector`

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector`

Change assertInternalType()/assertNotInternalType() method to new specific alternatives

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

### `TestListenerToHooksRector`

- class: `Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector`

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

<br>

### `TryCatchToExpectExceptionRector`

- class: `Rector\PHPUnit\Rector\TryCatchToExpectExceptionRector`

Turns try/catch to expectException() call

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

<br>

### `UseSpecificWillMethodRector`

- class: `Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector`

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

<br>

## Php

### `AddDefaultValueForUndefinedVariableRector`

- class: `Rector\Php\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector`

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

<br>

### `ArrayKeyExistsOnPropertyRector`

- class: `Rector\Php\Rector\FuncCall\ArrayKeyExistsOnPropertyRector`

Change array_key_exists() on property to property_exists()

```diff
 class SomeClass {
      public $value;
 }
 $someClass = new SomeClass;

-array_key_exists('value', $someClass);
+property_exists($someClass, 'value');
```

<br>

### `ArrayKeyFirstLastRector`

- class: `Rector\Php\Rector\FuncCall\ArrayKeyFirstLastRector`

Make use of array_key_first() and array_key_last()

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

<br>

### `ArraySpreadInsteadOfArrayMergeRector`

- class: `Rector\Php\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector`

Change array_merge() to spread operator, except values with possible string key values

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

### `AssignArrayToStringRector`

- class: `Rector\Php\Rector\Assign\AssignArrayToStringRector`

String cannot be turned into array by assignment anymore

```diff
-$string = '';
+$string = [];
 $string[] = 1;
```

<br>

### `BarewordStringRector`

- class: `Rector\Php\Rector\ConstFetch\BarewordStringRector`

Changes unquoted non-existing constants to strings

```diff
-var_dump(VAR);
+var_dump("VAR");
```

<br>

### `BinaryOpBetweenNumberAndStringRector`

- class: `Rector\Php\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector`

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

         $name = 'Tom';
-        $value = 5 * $name;
+        $value = 5 * 0;
     }
 }
```

<br>

### `BreakNotInLoopOrSwitchToReturnRector`

- class: `Rector\Php\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector`

Convert break outside for/foreach/switch context to return

```diff
 class SomeClass
 {
     public function run()
     {
         $zhrs = abs($gmt)/3600;
         $hrs = floor($zhrs);
         if ($isphp5)
             return sprintf('%s%02d%02d',($gmt<=0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
         else
             return sprintf('%s%02d%02d',($gmt<0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
-        break;
+        return;
     }
 }
```

<br>

### `CallUserMethodRector`

- class: `Rector\Php\Rector\FuncCall\CallUserMethodRector`

Changes call_user_method()/call_user_method_array() to call_user_func()/call_user_func_array()

```diff
-call_user_method($method, $obj, "arg1", "arg2");
+call_user_func(array(&$obj, "method"), "arg1", "arg2");
```

<br>

### `ClassConstantToSelfClassRector`

- class: `Rector\Php\Rector\MagicConstClass\ClassConstantToSelfClassRector`

Change __CLASS__ to self::class

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

### `ClosureToArrowFunctionRector`

- class: `Rector\Php\Rector\Closure\ClosureToArrowFunctionRector`

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

<br>

### `CompleteVarDocTypePropertyRector`

- class: `Rector\Php\Rector\Property\CompleteVarDocTypePropertyRector`

Complete property `@var` annotations for missing one, yet known.

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

### `ContinueToBreakInSwitchRector`

- class: `Rector\Php\Rector\Switch_\ContinueToBreakInSwitchRector`

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

<br>

### `CountOnNullRector`

- class: `Rector\Php\Rector\FuncCall\CountOnNullRector`

Changes count() on null to safe ternary check

```diff
 $values = null;
-$count = count($values);
+$count = is_array($values) || $values instanceof Countable ? count($values) : 0;
```

<br>

### `CreateFunctionToAnonymousFunctionRector`

- class: `Rector\Php\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector`

Use anonymous functions instead of deprecated create_function()

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

### `EmptyListRector`

- class: `Rector\Php\Rector\List_\EmptyListRector`

list() cannot be empty

```diff
-list() = $values;
+list($generated) = $values;
```

<br>

### `EregToPregMatchRector`

- class: `Rector\Php\Rector\FuncCall\EregToPregMatchRector`

Changes ereg*() to preg*() calls

```diff
-ereg("hi")
+preg_match("#hi#");
```

<br>

### `ExceptionHandlerTypehintRector`

- class: `Rector\Php\Rector\FunctionLike\ExceptionHandlerTypehintRector`

Changes property `@var` annotations from annotation to type.

```diff
-function handler(Exception $exception) { ... }
+function handler(Throwable $exception) { ... }
 set_exception_handler('handler');
```

<br>

### `ExportToReflectionFunctionRector`

- class: `Rector\Php\Rector\StaticCall\ExportToReflectionFunctionRector`

Change export() to ReflectionFunction alternatives

```diff
-$reflectionFunction = ReflectionFunction::export('foo');
-$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
+$reflectionFunction = new ReflectionFunction('foo');
+$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
```

<br>

### `FilterVarToAddSlashesRector`

- class: `Rector\Php\Rector\FuncCall\FilterVarToAddSlashesRector`

Change filter_var() with slash escaping to addslashes()

```diff
 $var= "Satya's here!";
-filter_var($var, FILTER_SANITIZE_MAGIC_QUOTES);
+addslashes($var);
```

<br>

### `GetCalledClassToStaticClassRector`

- class: `Rector\Php\Rector\FuncCall\GetCalledClassToStaticClassRector`

Change __CLASS__ to self::class

```diff
 class SomeClass
-{
-   public function callOnMe()
-   {
-       var_dump( get_called_class());
-   }
-}
+    {
+       public function callOnMe()
+       {
+           var_dump( static::class);
+       }
+    }
```

<br>

### `GetClassOnNullRector`

- class: `Rector\Php\Rector\FuncCall\GetClassOnNullRector`

Null is no more allowed in get_class()

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

### `IfToSpaceshipRector`

- class: `Rector\Php\Rector\If_\IfToSpaceshipRector`

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

<br>

### `IsCountableRector`

- class: `Rector\Php\Rector\BinaryOp\IsCountableRector`

Changes is_array + Countable check to is_countable

```diff
-is_array($foo) || $foo instanceof Countable;
+is_countable($foo);
```

<br>

### `IsIterableRector`

- class: `Rector\Php\Rector\BinaryOp\IsIterableRector`

Changes is_array + Traversable check to is_iterable

```diff
-is_array($foo) || $foo instanceof Traversable;
+is_iterable($foo);
```

<br>

### `IsObjectOnIncompleteClassRector`

- class: `Rector\Php\Rector\FuncCall\IsObjectOnIncompleteClassRector`

Incomplete class returns inverted bool on is_object()

```diff
 $incompleteObject = new __PHP_Incomplete_Class;
-$isObject = is_object($incompleteObject);
+$isObject = ! is_object($incompleteObject);
```

<br>

### `JsonThrowOnErrorRector`

- class: `Rector\Php\Rector\FuncCall\JsonThrowOnErrorRector`

Adds JSON_THROW_ON_ERROR to json_encode() and json_decode() to throw JsonException on error

```diff
-json_encode($content);
-json_decode($json);
+json_encode($content, JSON_THROW_ON_ERROR);
+json_decode($json, null, null, JSON_THROW_ON_ERROR);
```

<br>

### `ListEachRector`

- class: `Rector\Php\Rector\Each\ListEachRector`

each() function is deprecated, use key() and current() instead

```diff
-list($key, $callback) = each($callbacks);
+$key = key($opt->option);
+$val = current($opt->option);
```

<br>

### `ListSplitStringRector`

- class: `Rector\Php\Rector\List_\ListSplitStringRector`

list() cannot split string directly anymore, use str_split()

```diff
-list($foo) = "string";
+list($foo) = str_split("string");
```

<br>

### `ListSwapArrayOrderRector`

- class: `Rector\Php\Rector\List_\ListSwapArrayOrderRector`

list() assigns variables in reverse order - relevant in array assign

```diff
-list($a[], $a[]) = [1, 2];
+list($a[], $a[]) = array_reverse([1, 2]);
```

<br>

### `MbStrrposEncodingArgumentPositionRector`

- class: `Rector\Php\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector`

Change mb_strrpos() encoding argument position

```diff
-mb_strrpos($text, "abc", "UTF-8");
+mb_strrpos($text, "abc", 0, "UTF-8");
```

<br>

### `MultiDirnameRector`

- class: `Rector\Php\Rector\FuncCall\MultiDirnameRector`

Changes multiple dirname() calls to one with nesting level

```diff
-dirname(dirname($path));
+dirname($path, 2);
```

<br>

### `MultiExceptionCatchRector`

- class: `Rector\Php\Rector\TryCatch\MultiExceptionCatchRector`

Changes multi catch of same exception to single one | separated.

```diff
 try {
    // Some code...
-} catch (ExceptionType1 $exception) {
-   $sameCode;
-} catch (ExceptionType2 $exception) {
+} catch (ExceptionType1 | ExceptionType2 $exception) {
    $sameCode;
 }
```

<br>

### `MysqlAssignToMysqliRector`

- class: `Rector\Php\Rector\Assign\MysqlAssignToMysqliRector`

Converts more complex mysql functions to mysqli

```diff
-$data = mysql_db_name($result, $row);
+mysqli_data_seek($result, $row);
+$fetch = mysql_fetch_row($result);
+$data = $fetch[0];
```

<br>

### `NullCoalescingOperatorRector`

- class: `Rector\Php\Rector\Assign\NullCoalescingOperatorRector`

Use null coalescing operator ??=

```diff
 $array = [];
-$array['user_id'] = $array['user_id'] ?? 'value';
+$array['user_id'] ??= 'value';
```

<br>

### `ParseStrWithResultArgumentRector`

- class: `Rector\Php\Rector\FuncCall\ParseStrWithResultArgumentRector`

Use $result argument in parse_str() function

```diff
-parse_str($this->query);
-$data = get_defined_vars();
+parse_str($this->query, $result);
+$data = $result;
```

<br>

### `Php4ConstructorRector`

- class: `Rector\Php\Rector\FunctionLike\Php4ConstructorRector`

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

<br>

### `PowToExpRector`

- class: `Rector\Php\Rector\FuncCall\PowToExpRector`

Changes pow(val, val2) to ** (exp) parameter

```diff
-pow(1, 2);
+1**2;
```

<br>

### `PreferThisOrSelfMethodCallRector`

- class: `Rector\Php\Rector\MethodCall\PreferThisOrSelfMethodCallRector`

Changes $this->... to self:: or vise versa for specific types

```yaml
services:
    Rector\Php\Rector\MethodCall\PreferThisOrSelfMethodCallRector:
        PHPUnit\TestCase: self
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

<br>

### `PregReplaceEModifierRector`

- class: `Rector\Php\Rector\FuncCall\PregReplaceEModifierRector`

The /e modifier is no longer supported, use preg_replace_callback instead

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

### `PublicConstantVisibilityRector`

- class: `Rector\Php\Rector\ClassConst\PublicConstantVisibilityRector`

Add explicit public constant visibility.

```diff
 class SomeClass
 {
-    const HEY = 'you';
+    public const HEY = 'you';
 }
```

<br>

### `RandomFunctionRector`

- class: `Rector\Php\Rector\FuncCall\RandomFunctionRector`

Changes rand, srand and getrandmax by new md_* alternatives.

```diff
-rand();
+mt_rand();
```

<br>

### `RealToFloatTypeCastRector`

- class: `Rector\Php\Rector\Double\RealToFloatTypeCastRector`

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

<br>

### `ReduceMultipleDefaultSwitchRector`

- class: `Rector\Php\Rector\Switch_\ReduceMultipleDefaultSwitchRector`

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

<br>

### `RegexDashEscapeRector`

- class: `Rector\Php\Rector\FuncCall\RegexDashEscapeRector`

Escape - in some cases

```diff
-preg_match("#[\w-()]#", 'some text');
+preg_match("#[\w\-()]#", 'some text');
```

<br>

### `RemoveExtraParametersRector`

- class: `Rector\Php\Rector\FuncCall\RemoveExtraParametersRector`

Remove extra parameters

```diff
-strlen("asdf", 1);
+strlen("asdf");
```

<br>

### `RemoveMissingCompactVariableRector`

- class: `Rector\Php\Rector\FuncCall\RemoveMissingCompactVariableRector`

Remove non-existing vars from compact()

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

<br>

### `RemoveReferenceFromCallRector`

- class: `Rector\Php\Rector\FuncCall\RemoveReferenceFromCallRector`

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

<br>

### `RenameConstantRector`

- class: `Rector\Php\Rector\ConstFetch\RenameConstantRector`

Replace constant by new ones

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

### `RenameMktimeWithoutArgsToTimeRector`

- class: `Rector\Php\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector`

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

### `ReservedFnFunctionRector`

- class: `Rector\Php\Rector\Function_\ReservedFnFunctionRector`

Change fn() function name, since it will be reserved keyword

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

### `ReservedObjectRector`

- class: `Rector\Php\Rector\Name\ReservedObjectRector`

Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured

```diff
-class Object
+class SmartObject
 {
 }
```

<br>

### `SensitiveConstantNameRector`

- class: `Rector\Php\Rector\ConstFetch\SensitiveConstantNameRector`

Changes case insensitive constants to sensitive ones.

```diff
 define('FOO', 42, true);
 var_dump(FOO);
-var_dump(foo);
+var_dump(FOO);
```

<br>

### `SensitiveDefineRector`

- class: `Rector\Php\Rector\FuncCall\SensitiveDefineRector`

Changes case insensitive constants to sensitive ones.

```diff
-define('FOO', 42, true);
+define('FOO', 42);
```

<br>

### `SensitiveHereNowDocRector`

- class: `Rector\Php\Rector\String_\SensitiveHereNowDocRector`

Changes heredoc/nowdoc that contains closing word to safe wrapper name

```diff
-$value = <<<A
+$value = <<<A_WRAP
     A
-A
+A_WRAP
```

<br>

### `StaticCallOnNonStaticToInstanceCallRector`

- class: `Rector\Php\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector`

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

<br>

### `StringClassNameToClassConstantRector`

- class: `Rector\Php\Rector\String_\StringClassNameToClassConstantRector`

Replace string class names by <class>::class constant

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

### `StringifyDefineRector`

- class: `Rector\Php\Rector\FuncCall\StringifyDefineRector`

Make first argument of define() string

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

### `StringifyStrNeedlesRector`

- class: `Rector\Php\Rector\FuncCall\StringifyStrNeedlesRector`

Makes needles explicit strings

```diff
 $needle = 5;
-$fivePosition = strpos('725', $needle);
+$fivePosition = strpos('725', (string) $needle);
```

<br>

### `StringsAssertNakedRector`

- class: `Rector\Php\Rector\FuncCall\StringsAssertNakedRector`

String asserts must be passed directly to assert()

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

### `SwapFuncCallArgumentsRector`

- class: `Rector\Php\Rector\FuncCall\SwapFuncCallArgumentsRector`

Swap arguments in function calls

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

### `TernaryToNullCoalescingRector`

- class: `Rector\Php\Rector\Ternary\TernaryToNullCoalescingRector`

Changes unneeded null check to ?? operator

```diff
-$value === null ? 10 : $value;
+$value ?? 10;
```

```diff
-isset($value) ? $value : 10;
+$value ?? 10;
```

<br>

### `TernaryToSpaceshipRector`

- class: `Rector\Php\Rector\Ternary\TernaryToSpaceshipRector`

Use <=> spaceship instead of ternary with same effect

```diff
 function order_func($a, $b) {
-    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
+    return $a <=> $b;
 }
```

<br>

### `ThisCallOnStaticMethodToStaticCallRector`

- class: `Rector\Php\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector`

Changes $this->call() to static method to static call

```diff
 class SomeClass
 {
     public static function run()
     {
-        $this->eat();
+        self::eat();
     }

     public static function eat()
     {
     }
 }
```

<br>

### `TypedPropertyRector`

- class: `Rector\Php\Rector\Property\TypedPropertyRector`

Changes property `@var` annotations from annotation to type.

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

### `UnsetCastRector`

- class: `Rector\Php\Rector\Unset_\UnsetCastRector`

Removes (unset) cast

```diff
-$value = (unset) $value;
+$value = null;
```

<br>

### `VarToPublicPropertyRector`

- class: `Rector\Php\Rector\Property\VarToPublicPropertyRector`

Remove unused private method

```diff
 final class SomeController
 {
-    var $name = 'Tom';
+    public $name = 'Tom';
 }
```

<br>

### `WhileEachToForeachRector`

- class: `Rector\Php\Rector\Each\WhileEachToForeachRector`

each() function is deprecated, use foreach() instead.

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

<br>

## PhpParser

### `CatchAndClosureUseNameRector`

- class: `Rector\PhpParser\Rector\CatchAndClosureUseNameRector`

Turns `$catchNode->var` to its new `name` property in php-parser

```diff
-$catchNode->var;
+$catchNode->var->name
```

<br>

### `IdentifierRector`

- class: `Rector\PhpParser\Rector\IdentifierRector`

Turns node string names to Identifier object in php-parser

```diff
 $constNode = new PhpParser\Node\Const_;
-$name = $constNode->name;
+$name = $constNode->name->toString();'
```

<br>

### `ParamAndStaticVarNameRector`

- class: `Rector\PhpParser\Rector\ParamAndStaticVarNameRector`

Turns old string `var` to `var->name` sub-variable in Node of PHP-Parser

```diff
-$paramNode->name;
+$paramNode->var->name;
```

```diff
-$staticVarNode->name;
+$staticVarNode->var->name;
```

<br>

### `RemoveNodeRector`

- class: `Rector\PhpParser\Rector\RemoveNodeRector`

Turns integer return to remove node to constant in NodeVisitor of PHP-Parser

```diff
 public function leaveNode()
 {
-    return false;
+    return NodeTraverser::REMOVE_NODE;
 }
```

<br>

### `SetLineRector`

- class: `Rector\PhpParser\Rector\SetLineRector`

Turns standalone line method to attribute in Node of PHP-Parser

```diff
-$node->setLine(5);
+$node->setAttribute("line", 5);
```

<br>

### `UseWithAliasRector`

- class: `Rector\PhpParser\Rector\UseWithAliasRector`

Turns use property to method and `$node->alias` to last name in UseAlias Node of PHP-Parser

```diff
-$node->alias;
+$node->getAlias();
```

```diff
-$node->name->getLast();
+$node->alias
```

<br>

## PhpSpecToPHPUnit

### `AddMockPropertiesRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
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

### `MockVariableToPropertyFetchRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\ClassMethod\MockVariableToPropertyFetchRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
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

### `PhpSpecClassToPHPUnitClassRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
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

### `PhpSpecMethodToPHPUnitMethodRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
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

### `PhpSpecMocksToPHPUnitMocksRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
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

### `PhpSpecPromisesToPHPUnitAssertRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
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

### `RenameSpecFileToTestFileRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\RenameSpecFileToTestFileRector`

Rename "*Spec.php" file to "*Test.php" file

<br>

## RemovingStatic

### `NewUniqueObjectToEntityFactoryRector`

- class: `Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector`

Convert new X to new factories

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

### `PHPUnitStaticToKernelTestCaseGetRector`

- class: `Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector`

Convert static calls in PHPUnit test cases, to get() from the container of KernelTestCase

```yaml
services:
    Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector:
        staticClassTypes:
            - EntityFactory
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
+
+    protected function setUp(): void
+    {
+        parent::setUp();
+        $this->entityFactory = self::$container->get(EntityFactory::class);
+    }

-final class SomeTestCase extends TestCase
-{
     public function test()
     {
-        $product = EntityFactory::create('product');
+        $product = $this->entityFactory->create('product');
     }
 }
```

<br>

### `PassFactoryToUniqueObjectRector`

- class: `Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector`

Convert new X/Static::call() to factories in entities, pass them via constructor to each other

```yaml
services:
    Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector:
        typesToServices:
            - StaticClass
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

### `StaticTypeToSetterInjectionRector`

- class: `Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector`

Changes types to setter injection

```yaml
services:
    Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector:
        $staticTypes:
            - SomeStaticClass
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
-}
+}
```

<br>

## SOLID

### `FinalizeClassesWithoutChildrenRector`

- class: `Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector`

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

<br>

### `MakeUnusedClassesWithChildrenAbstractRector`

- class: `Rector\SOLID\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector`

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

<br>

### `PrivatizeLocalClassConstantRector`

- class: `Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector`

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

<br>

## Sensio

### `TemplateAnnotationRector`

- class: `Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector`

Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony

```diff
-/**
- * @Template()
- */
 public function indexAction()
 {
+    return $this->render("index.html.twig");
 }
```

<br>

## Shopware

### `ReplaceEnlightResponseWithSymfonyResponseRector`

- class: `Rector\Shopware\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector`

Replace Enlight Response methods with Symfony Response methods

```diff
 class FrontendController extends \Enlight_Controller_Action
 {
     public function run()
     {
-        $this->Response()->setHeader('Foo', 'Yea');
+        $this->Response()->headers->set('Foo', 'Yea');
     }
 }
```

<br>

### `ShopRegistrationServiceRector`

- class: `Rector\Shopware\Rector\MethodCall\ShopRegistrationServiceRector`

Replace $shop->registerResources() with ShopRegistrationService

```diff
 class SomeClass
 {
     public function run()
     {
         $shop = new \Shopware\Models\Shop\Shop();
-        $shop->registerResources();
+        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerShop($shop);
     }
 }
```

<br>

### `ShopwareVersionConstsRector`

- class: `Rector\Shopware\Rector\ClassConstFetch\ShopwareVersionConstsRector`

Use version from di parameter

```diff
 class SomeClass
 {
     public function run()
     {
-        echo \Shopware::VERSION;
+        echo Shopware()->Container()->getParameter('shopware.release.version');
     }
 }
```

<br>

## Silverstripe

### `ConstantToStaticCallRector`

- class: `Rector\Silverstripe\Rector\ConstantToStaticCallRector`

Turns defined constant to static method call.

```diff
-SS_DATABASE_NAME;
+Environment::getEnv("SS_DATABASE_NAME");
```

<br>

### `DefineConstantToStaticCallRector`

- class: `Rector\Silverstripe\Rector\DefineConstantToStaticCallRector`

Turns defined function call to static method call.

```diff
-defined("SS_DATABASE_NAME");
+Environment::getEnv("SS_DATABASE_NAME");
```

<br>

## Sylius

### `ReplaceCreateMethodWithoutReviewerRector`

- class: `Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector`

Turns `createForSubjectWithReviewer()` with null review to standalone method in Sylius

```diff
-$this->createForSubjectWithReviewer($subject, null)
+$this->createForSubject($subject)
```

<br>

## Symfony

### `ActionSuffixRemoverRector`

- class: `Rector\Symfony\Rector\Controller\ActionSuffixRemoverRector`

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

<br>

### `AddFlashRector`

- class: `Rector\Symfony\Rector\Controller\AddFlashRector`

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

<br>

### `CascadeValidationFormBuilderRector`

- class: `Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector`

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

<br>

### `ConsoleExceptionToErrorEventConstantRector`

- class: `Rector\Symfony\Rector\Console\ConsoleExceptionToErrorEventConstantRector`

Turns old event name with EXCEPTION to ERROR constant in Console in Symfony

```diff
-"console.exception"
+Symfony\Component\Console\ConsoleEvents::ERROR
```

```diff
-Symfony\Component\Console\ConsoleEvents::EXCEPTION
+Symfony\Component\Console\ConsoleEvents::ERROR
```

<br>

### `ConstraintUrlOptionRector`

- class: `Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector`

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

<br>

### `ContainerBuilderCompileEnvArgumentRector`

- class: `Rector\Symfony\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector`

Turns old default value to parameter in ContinerBuilder->build() method in DI in Symfony

```diff
-$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile();
+$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile(true);
```

<br>

### `ContainerGetToConstructorInjectionRector`

- class: `Rector\Symfony\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector`

Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony

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

### `FormIsValidRector`

- class: `Rector\Symfony\Rector\Form\FormIsValidRector`

Adds `$form->isSubmitted()` validatoin to all `$form->isValid()` calls in Form in Symfony

```diff
-if ($form->isValid()) {
+if ($form->isSubmitted() && $form->isValid()) {
 }
```

<br>

### `FormTypeGetParentRector`

- class: `Rector\Symfony\Rector\Form\FormTypeGetParentRector`

Turns string Form Type references to their CONSTANT alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony

```diff
-function getParent() { return "collection"; }
+function getParent() { return CollectionType::class; }
```

```diff
-function getExtendedType() { return "collection"; }
+function getExtendedType() { return CollectionType::class; }
```

<br>

### `FormTypeInstanceToClassConstRector`

- class: `Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector`

Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"

```diff
 class SomeController
 {
     public function action()
     {
-        $form = $this->createForm(new TeamType, $entity, [
+        $form = $this->createForm(TeamType::class, $entity, [
             'action' => $this->generateUrl('teams_update', ['id' => $entity->getId()]),
             'method' => 'PUT',
-        ]);
+        ));
     }
 }
```

<br>

### `GetParameterToConstructorInjectionRector`

- class: `Rector\Symfony\Rector\FrameworkBundle\GetParameterToConstructorInjectionRector`

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

<br>

### `GetRequestRector`

- class: `Rector\Symfony\Rector\HttpKernel\GetRequestRector`

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

<br>

### `GetToConstructorInjectionRector`

- class: `Rector\Symfony\Rector\FrameworkBundle\GetToConstructorInjectionRector`

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

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

### `MakeCommandLazyRector`

- class: `Rector\Symfony\Rector\Class_\MakeCommandLazyRector`

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

<br>

### `MakeDispatchFirstArgumentEventRector`

- class: `Rector\Symfony\Rector\MethodCall\MakeDispatchFirstArgumentEventRector`

Make event object a first argument of dispatch() method, event name as second

```diff
 use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

### `OptionNameRector`

- class: `Rector\Symfony\Rector\Form\OptionNameRector`

Turns old option names to new ones in FormTypes in Form in Symfony

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

<br>

### `ParseFileRector`

- class: `Rector\Symfony\Rector\Yaml\ParseFileRector`

session > use_strict_mode is true by default and can be removed

```diff
-session > use_strict_mode: true
+session:
```

<br>

### `ProcessBuilderGetProcessRector`

- class: `Rector\Symfony\Rector\Process\ProcessBuilderGetProcessRector`

Removes `$processBuilder->getProcess()` calls to $processBuilder in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.

```diff
 $processBuilder = new Symfony\Component\Process\ProcessBuilder;
-$process = $processBuilder->getProcess();
-$commamdLine = $processBuilder->getProcess()->getCommandLine();
+$process = $processBuilder;
+$commamdLine = $processBuilder->getCommandLine();
```

<br>

### `ProcessBuilderInstanceRector`

- class: `Rector\Symfony\Rector\Process\ProcessBuilderInstanceRector`

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

<br>

### `ReadOnlyOptionToAttributeRector`

- class: `Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector`

Change "read_only" option in form to attribute

```diff
 use Symfony\Component\Form\FormBuilderInterface;

 function buildForm(FormBuilderInterface $builder, array $options)
 {
-    $builder->add('cuid', TextType::class, ['read_only' => true]);
+    $builder->add('cuid', TextType::class, ['attr' => [read_only' => true]]);
 }
```

<br>

### `RedirectToRouteRector`

- class: `Rector\Symfony\Rector\Controller\RedirectToRouteRector`

Turns redirect to route to short helper method in Controller in Symfony

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

<br>

### `ResponseStatusCodeRector`

- class: `Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector`

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

<br>

### `RootNodeTreeBuilderRector`

- class: `Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector`

Changes  Process string argument to an array

```diff
 use Symfony\Component\Config\Definition\Builder\TreeBuilder;

-$treeBuilder = new TreeBuilder();
-$rootNode = $treeBuilder->root('acme_root');
+$treeBuilder = new TreeBuilder('acme_root');
+$rootNode = $treeBuilder->getRootNode();
 $rootNode->someCall();
```

<br>

### `SimplifyWebTestCaseAssertionsRector`

- class: `Rector\Symfony\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector`

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

<br>

### `StringFormTypeToClassRector`

- class: `Rector\Symfony\Rector\Form\StringFormTypeToClassRector`

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony

```diff
 $formBuilder = new Symfony\Component\Form\FormBuilder;
-$formBuilder->add('name', 'form.type.text');
+$form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

<br>

### `StringToArrayArgumentProcessRector`

- class: `Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector`

Changes Process string argument to an array

```diff
 use Symfony\Component\Process\Process;
-$process = new Process('ls -l');
+$process = new Process(['ls', '-l']);
```

<br>

### `VarDumperTestTraitMethodArgsRector`

- class: `Rector\Symfony\Rector\VarDumper\VarDumperTestTraitMethodArgsRector`

Adds new `$format` argument in `VarDumperTestTrait->assertDumpEquals()` in Validator in Symfony.

```diff
-$varDumperTestTrait->assertDumpEquals($dump, $data, $mesage = "");
+$varDumperTestTrait->assertDumpEquals($dump, $data, $context = null, $mesage = "");
```

```diff
-$varDumperTestTrait->assertDumpMatchesFormat($dump, $format, $mesage = "");
+$varDumperTestTrait->assertDumpMatchesFormat($dump, $format, $context = null,  $mesage = "");
```

<br>

## SymfonyPHPUnit

### `MultipleServiceGetToSetUpMethodRector`

- class: `Rector\SymfonyPHPUnit\Rector\Class_\MultipleServiceGetToSetUpMethodRector`

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

<br>

## Twig

### `SimpleFunctionAndFilterRector`

- class: `Rector\Twig\Rector\SimpleFunctionAndFilterRector`

Changes Twig_Function_Method to Twig_SimpleFunction calls in TwigExtension.

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

## TypeDeclaration

### `AddClosureReturnTypeRector`

- class: `Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector`

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

<br>

### `ParamTypeDeclarationRector`

- class: `Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector`

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

<br>

### `ReturnTypeDeclarationRector`

- class: `Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector`

Change @return types and type from static analysis to type declarations if not a BC-break

```diff
 <?php

 class SomeClass
 {
     /**
      * @return int
      */
-    public function getCount()
+    public function getCount(): int
     {
     }
 }
```

<br>

---
## General

- [Core](#core)

## Core

### `ActionInjectionToConstructorInjectionRector`

- class: `Rector\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector`

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

<br>

### `AddMethodParentCallRector`

- class: `Rector\Rector\ClassMethod\AddMethodParentCallRector`

Add method parent call, in case new parent method is added

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

### `AddReturnTypeDeclarationRector`

- class: `Rector\Rector\ClassMethod\AddReturnTypeDeclarationRector`

Changes defined return typehint of method and class.

```yaml
services:
    Rector\Rector\ClassMethod\AddReturnTypeDeclarationRector:
        SomeClass:
            getData: array
```

↓

```diff
 class SomeClass
 {
-    public getData();
+    public getData(): array;
 }
```

<br>

### `AnnotatedPropertyInjectToConstructorInjectionRector`

- class: `Rector\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector`

Turns non-private properties with `@annotation` to private properties and constructor injection

```yaml
services:
    Rector\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector:
        $annotation: inject
```

↓

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

### `ArgumentAdderRector`

- class: `Rector\Rector\Argument\ArgumentAdderRector`

This Rector adds new default arguments in calls of defined methods and class types.

```yaml
services:
    Rector\Rector\Argument\ArgumentAdderRector:
        SomeExampleClass:
            someMethod:
                -
                    name: someArgument
                    default_value: 'true'
                    type: SomeType
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->someMethod();
+$someObject->someMethod(true);
```

```yaml
services:
    Rector\Rector\Argument\ArgumentAdderRector:
        SomeExampleClass:
            someMethod:
                -
                    name: someArgument
                    default_value: 'true'
                    type: SomeType
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

### `ArgumentDefaultValueReplacerRector`

- class: `Rector\Rector\Argument\ArgumentDefaultValueReplacerRector`

Replaces defined map of arguments in defined methods and their calls.

```yaml
services:
    Rector\Rector\Argument\ArgumentDefaultValueReplacerRector:
        SomeExampleClass:
            someMethod:
                -
                    -
                        before: 'SomeClass::OLD_CONSTANT'
                        after: 'false'
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(SomeClass::OLD_CONSTANT);
+$someObject->someMethod(false);'
```

<br>

### `ArgumentRemoverRector`

- class: `Rector\Rector\Argument\ArgumentRemoverRector`

Removes defined arguments in defined methods and their calls.

```yaml
services:
    Rector\Rector\Argument\ArgumentRemoverRector:
        ExampleClass:
            someMethod:
                -
                    value: 'true'
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(true);
+$someObject->someMethod();'
```

<br>

### `ChangeConstantVisibilityRector`

- class: `Rector\Rector\Visibility\ChangeConstantVisibilityRector`

Change visibility of constant from parent class.

```yaml
services:
    Rector\Rector\Visibility\ChangeConstantVisibilityRector:
        ParentObject:
            SOME_CONSTANT: protected
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

### `ChangeMethodVisibilityRector`

- class: `Rector\Rector\Visibility\ChangeMethodVisibilityRector`

Change visibility of method from parent class.

```yaml
services:
    Rector\Rector\Visibility\ChangeMethodVisibilityRector:
        FrameworkClass:
            someMethod: protected
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

### `ChangePropertyVisibilityRector`

- class: `Rector\Rector\Visibility\ChangePropertyVisibilityRector`

Change visibility of property from parent class.

```yaml
services:
    Rector\Rector\Visibility\ChangePropertyVisibilityRector:
        FrameworkClass:
            someProperty: protected
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

### `FluentReplaceRector`

- class: `Rector\Rector\MethodBody\FluentReplaceRector`

Turns fluent interface calls to classic ones.

```yaml
services:
    Rector\Rector\MethodBody\FluentReplaceRector:
        -
            - SomeExampleClass
```

↓

```diff
 $someClass = new SomeClass();
-$someClass->someFunction()
-            ->otherFunction();
+$someClass->someFunction();
+$someClass->otherFunction();
```

<br>

### `FunctionToMethodCallRector`

- class: `Rector\Rector\Function_\FunctionToMethodCallRector`

Turns defined function calls to local method calls.

```yaml
services:
    Rector\Rector\Function_\FunctionToMethodCallRector:
        view:
            - this
            - render
```

↓

```diff
-view("...", []);
+$this->render("...", []);
```

<br>

### `FunctionToNewRector`

- class: `Rector\Rector\FuncCall\FunctionToNewRector`

Change configured function calls to new Instance

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

### `FunctionToStaticCallRector`

- class: `Rector\Rector\Function_\FunctionToStaticCallRector`

Turns defined function call to static method call.

```yaml
services:
    Rector\Rector\Function_\FunctionToStaticCallRector:
        view:
            - SomeStaticClass
            - render
```

↓

```diff
-view("...", []);
+SomeClass::render("...", []);
```

<br>

### `GetAndSetToMethodCallRector`

- class: `Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector`

Turns defined `__get`/`__set` to specific method calls.

```yaml
services:
    Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector:
        SomeContainer:
            set: addService
```

↓

```diff
 $container = new SomeContainer;
-$container->someService = $someService;
+$container->setService("someService", $someService);
```

```yaml
services:
    Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector:
        $typeToMethodCalls:
            SomeContainer:
                get: getService
```

↓

```diff
 $container = new SomeContainer;
-$someService = $container->someService;
+$someService = $container->getService("someService");
```

<br>

### `InjectAnnotationClassRector`

- class: `Rector\Rector\Property\InjectAnnotationClassRector`

Changes properties with specified annotations class to constructor injection

```yaml
services:
    Rector\Rector\Property\InjectAnnotationClassRector:
        $annotationClasses:
            - JMS\DiExtraBundle\Annotation\Inject
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

### `MergeInterfacesRector`

- class: `Rector\Rector\Interface_\MergeInterfacesRector`

Merges old interface to a new one, that already has its methods

```yaml
services:
    Rector\Rector\Interface_\MergeInterfacesRector:
        SomeOldInterface: SomeInterface
```

↓

```diff
-class SomeClass implements SomeInterface, SomeOldInterface
+class SomeClass implements SomeInterface
 {
 }
```

<br>

### `MethodCallToAnotherMethodCallWithArgumentsRector`

- class: `Rector\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector`

Turns old method call with specfici type to new one with arguments

```yaml
services:
    Rector\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector:
        Nette\DI\ServiceDefinition:
            setInject:
                -
                    - addTag
                    -
                        - inject
```

↓

```diff
 $serviceDefinition = new Nette\DI\ServiceDefinition;
-$serviceDefinition->setInject();
+$serviceDefinition->addTag('inject');
```

<br>

### `MoveRepositoryFromParentToConstructorRector`

- class: `Rector\Rector\Architecture\RepositoryAsService\MoveRepositoryFromParentToConstructorRector`

Turns parent EntityRepository class to constructor dependency

```yaml
services:
    Rector\Rector\Architecture\RepositoryAsService\MoveRepositoryFromParentToConstructorRector:
        $entityRepositoryClass: Doctrine\ORM\EntityRepository
        $entityManagerClass: Doctrine\ORM\EntityManager
```

↓

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

### `MultipleClassFileToPsr4ClassesRector`

- class: `Rector\Rector\Psr4\MultipleClassFileToPsr4ClassesRector`

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

<br>

### `NewObjectToFactoryCreateRector`

- class: `Rector\Rector\Architecture\Factory\NewObjectToFactoryCreateRector`

Replaces creating object instances with "new" keyword with factory method.

```yaml
services:
    Rector\Rector\Architecture\Factory\NewObjectToFactoryCreateRector:
        MyClass:
            class: MyClassFactory
            method: create
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

### `NewToStaticCallRector`

- class: `Rector\Rector\New_\NewToStaticCallRector`

Change new Object to static call

```yaml
services:
    Rector\Rector\New_\NewToStaticCallRector:
        Cookie:
            -
                - Cookie
                - create
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

### `NormalToFluentRector`

- class: `Rector\Rector\MethodBody\NormalToFluentRector`

Turns fluent interface calls to classic ones.

```yaml
services:
    Rector\Rector\MethodBody\NormalToFluentRector:
        SomeClass:
            - someFunction
            - otherFunction
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

### `ParentClassToTraitsRector`

- class: `Rector\Rector\Class_\ParentClassToTraitsRector`

Replaces parent class to specific traits

```yaml
services:
    Rector\Rector\Class_\ParentClassToTraitsRector:
        Nette\Object:
            - Nette\SmartObject
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

### `ParentTypehintedArgumentRector`

- class: `Rector\Rector\Typehint\ParentTypehintedArgumentRector`

Changes defined parent class typehints.

```yaml
services:
    Rector\Rector\Typehint\ParentTypehintedArgumentRector:
        SomeInterface:
            read:
                $content: string
```

↓

```diff
 interface SomeInterface
 {
     public read(string $content);
 }

 class SomeClass implements SomeInterface
 {
-    public read($content);
+    public read(string $content);
 }
```

<br>

### `PropertyAssignToMethodCallRector`

- class: `Rector\Rector\Assign\PropertyAssignToMethodCallRector`

Turns property assign of specific type and property name to method call

```yaml
services:
    Rector\Rector\Assign\PropertyAssignToMethodCallRector:
        $oldPropertiesToNewMethodCallsByType:
            SomeClass:
                oldPropertyName: oldProperty
                newMethodName: newMethodCall
```

↓

```diff
-$someObject = new SomeClass;
-$someObject->oldProperty = false;
+$someObject = new SomeClass;
+$someObject->newMethodCall(false);
```

<br>

### `PropertyToMethodRector`

- class: `Rector\Rector\Property\PropertyToMethodRector`

Replaces properties assign calls be defined methods.

```yaml
services:
    Rector\Rector\Property\PropertyToMethodRector:
        $perClassPropertyToMethods:
            SomeObject:
                property:
                    get: getProperty
                    set: setProperty
```

↓

```diff
-$result = $object->property;
-$object->property = $value;
+$result = $object->getProperty();
+$object->setProperty($value);
```

```yaml
services:
    Rector\Rector\Property\PropertyToMethodRector:
        $perClassPropertyToMethods:
            SomeObject:
                property:
                    get:
                        method: getConfig
                        arguments:
                            - someArg
```

↓

```diff
-$result = $object->property;
+$result = $object->getProperty('someArg');
```

<br>

### `PseudoNamespaceToNamespaceRector`

- class: `Rector\Rector\Namespace_\PseudoNamespaceToNamespaceRector`

Replaces defined Pseudo_Namespaces by Namespace\Ones.

```yaml
services:
    Rector\Rector\Namespace_\PseudoNamespaceToNamespaceRector:
        -
            Some_: {  }
```

↓

```diff
-$someService = new Some_Object;
+$someService = new Some\Object;
```

```yaml
services:
    Rector\Rector\Namespace_\PseudoNamespaceToNamespaceRector:
        -
            Some_:
                - Some_Class_To_Keep
```

↓

```diff
-/** @var Some_Object $someService */
-$someService = new Some_Object;
+/** @var Some\Object $someService */
+$someService = new Some\Object;
 $someClassToKeep = new Some_Class_To_Keep;
```

<br>

### `RemoveInterfacesRector`

- class: `Rector\Rector\Interface_\RemoveInterfacesRector`

Removes interfaces usage from class.

```yaml
services:
    Rector\Rector\Interface_\RemoveInterfacesRector:
        - SomeInterface
```

↓

```diff
-class SomeClass implements SomeInterface
+class SomeClass
 {
 }
```

<br>

### `RemoveTraitRector`

- class: `Rector\Rector\ClassLike\RemoveTraitRector`

Remove specific traits from code

```diff
 class SomeClass
 {
-    use SomeTrait;
 }
```

<br>

### `RenameAnnotationRector`

- class: `Rector\Rector\Annotation\RenameAnnotationRector`

Turns defined annotations above properties and methods to their new values.

```yaml
services:
    Rector\Rector\Annotation\RenameAnnotationRector:
        PHPUnit\Framework\TestCase:
            test: scenario
```

↓

```diff
 class SomeTest extends PHPUnit\Framework\TestCase
 {
-    /**
-     * @test
+    /**
+     * @scenario
      */
     public function someMethod()
     {
     }
 }
```

<br>

### `RenameClassConstantRector`

- class: `Rector\Rector\Constant\RenameClassConstantRector`

Replaces defined class constants in their calls.

```yaml
services:
    Rector\Rector\Constant\RenameClassConstantRector:
        SomeClass:
            OLD_CONSTANT: NEW_CONSTANT
            OTHER_OLD_CONSTANT: 'DifferentClass::NEW_CONSTANT'
```

↓

```diff
-$value = SomeClass::OLD_CONSTANT;
-$value = SomeClass::OTHER_OLD_CONSTANT;
+$value = SomeClass::NEW_CONSTANT;
+$value = DifferentClass::NEW_CONSTANT;
```

<br>

### `RenameClassConstantsUseToStringsRector`

- class: `Rector\Rector\Constant\RenameClassConstantsUseToStringsRector`

Replaces constant by value

```yaml
services:
    Rector\Rector\Constant\RenameClassConstantsUseToStringsRector:
        Nette\Configurator:
            DEVELOPMENT: development
            PRODUCTION: production
```

↓

```diff
-$value === Nette\Configurator::DEVELOPMENT
+$value === "development"
```

<br>

### `RenameClassRector`

- class: `Rector\Rector\Class_\RenameClassRector`

Replaces defined classes by new ones.

```yaml
services:
    Rector\Rector\Class_\RenameClassRector:
        App\SomeOldClass: App\SomeNewClass
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

### `RenameFunctionRector`

- class: `Rector\Rector\Function_\RenameFunctionRector`

Turns defined function call new one.

```yaml
services:
    Rector\Rector\Function_\RenameFunctionRector:
        view: Laravel\Templating\render
```

↓

```diff
-view("...", []);
+Laravel\Templating\render("...", []);
```

<br>

### `RenameMethodCallRector`

- class: `Rector\Rector\MethodCall\RenameMethodCallRector`

Turns method call names to new ones.

```yaml
services:
    Rector\Rector\MethodCall\RenameMethodCallRector:
        SomeExampleClass:
            oldMethod: newMethod
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->oldMethod();
+$someObject->newMethod();
```

<br>

### `RenameMethodRector`

- class: `Rector\Rector\MethodCall\RenameMethodRector`

Turns method names to new ones.

```yaml
services:
    Rector\Rector\MethodCall\RenameMethodRector:
        SomeExampleClass:
            oldMethod: newMethod
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->oldMethod();
+$someObject->newMethod();
```

<br>

### `RenameNamespaceRector`

- class: `Rector\Rector\Namespace_\RenameNamespaceRector`

Replaces old namespace by new one.

```yaml
services:
    Rector\Rector\Namespace_\RenameNamespaceRector:
        $oldToNewNamespaces:
            SomeOldNamespace: SomeNewNamespace
```

↓

```diff
-$someObject = new SomeOldNamespace\SomeClass;
+$someObject = new SomeNewNamespace\SomeClass;
```

<br>

### `RenamePropertyRector`

- class: `Rector\Rector\Property\RenamePropertyRector`

Replaces defined old properties by new ones.

```yaml
services:
    Rector\Rector\Property\RenamePropertyRector:
        $oldToNewPropertyByTypes:
            SomeClass:
                someOldProperty: someNewProperty
```

↓

```diff
-$someObject->someOldProperty;
+$someObject->someNewProperty;
```

<br>

### `RenameStaticMethodRector`

- class: `Rector\Rector\MethodCall\RenameStaticMethodRector`

Turns method names to new ones.

```yaml
services:
    Rector\Rector\MethodCall\RenameStaticMethodRector:
        SomeClass:
            oldMethod:
                - AnotherExampleClass
                - newStaticMethod
```

↓

```diff
-SomeClass::oldStaticMethod();
+AnotherExampleClass::newStaticMethod();
```

```yaml
services:
    Rector\Rector\MethodCall\RenameStaticMethodRector:
        $oldToNewMethodByClasses:
            SomeClass:
                oldMethod: newStaticMethod
```

↓

```diff
-SomeClass::oldStaticMethod();
+SomeClass::newStaticMethod();
```

<br>

### `ReplaceParentRepositoryCallsByRepositoryPropertyRector`

- class: `Rector\Rector\Architecture\RepositoryAsService\ReplaceParentRepositoryCallsByRepositoryPropertyRector`

Handles method calls in child of Doctrine EntityRepository and moves them to "$this->repository" property.

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

<br>

### `ReplaceVariableByPropertyFetchRector`

- class: `Rector\Rector\Architecture\DependencyInjection\ReplaceVariableByPropertyFetchRector`

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

<br>

### `ReturnThisRemoveRector`

- class: `Rector\Rector\MethodBody\ReturnThisRemoveRector`

Removes "return $this;" from *fluent interfaces* for specified classes.

```yaml
services:
    Rector\Rector\MethodBody\ReturnThisRemoveRector:
        -
            - SomeExampleClass
```

↓

```diff
 class SomeClass
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

### `ServiceLocatorToDIRector`

- class: `Rector\Rector\Architecture\RepositoryAsService\ServiceLocatorToDIRector`

Turns "$this->getRepository()" in Symfony Controller to constructor injection and private property access.

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

<br>

### `StaticCallToFunctionRector`

- class: `Rector\Rector\StaticCall\StaticCallToFunctionRector`

Turns static call to function call.

```yaml
services:
    Rector\Rector\StaticCall\StaticCallToFunctionRector:
        $staticCallToFunction:
            OldClass:
                oldMethod: new_function
```

↓

```diff
-OldClass::oldMethod("args");
+new_function("args");
```

<br>

### `StringToClassConstantRector`

- class: `Rector\Rector\String_\StringToClassConstantRector`

Changes strings to specific constants

```yaml
services:
    Rector\Rector\String_\StringToClassConstantRector:
        compiler.post_dump:
            - Yet\AnotherClass
            - CONSTANT
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

### `ToStringToMethodCallRector`

- class: `Rector\Rector\MagicDisclosure\ToStringToMethodCallRector`

Turns defined code uses of "__toString()" method  to specific method calls.

```yaml
services:
    Rector\Rector\MagicDisclosure\ToStringToMethodCallRector:
        SomeObject: getPath
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

### `UnsetAndIssetToMethodCallRector`

- class: `Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector`

Turns defined `__isset`/`__unset` calls to specific method calls.

```yaml
services:
    Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector:
        SomeContainer:
            isset: hasService
```

↓

```diff
 $container = new SomeContainer;
-isset($container["someKey"]);
+$container->hasService("someKey");
```

```yaml
services:
    Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector:
        SomeContainer:
            unset: removeService
```

↓

```diff
 $container = new SomeContainer;
-unset($container["someKey"]);
+$container->removeService("someKey");
```

<br>

### `WrapReturnRector`

- class: `Rector\Rector\ClassMethod\WrapReturnRector`

Wrap return value of specific method

```yaml
services:
    Rector\Rector\ClassMethod\WrapReturnRector:
        SomeClass:
            getItem: array
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

