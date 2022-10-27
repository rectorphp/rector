# 112 Rules Overview

## ArrowFunctionToAnonymousFunctionRector

Replace arrow functions with anonymous functions

- class: [`Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector`](../rules/DowngradePhp74/Rector/ArrowFunction/ArrowFunctionToAnonymousFunctionRector.php)

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

## DirConstToFileConstRector

Refactor __DIR__ to dirname(__FILE__)

- class: [`Rector\DowngradePhp53\Rector\Dir\DirConstToFileConstRector`](../rules/DowngradePhp53/Rector/Dir/DirConstToFileConstRector.php)

```diff
 final class SomeClass
 {
     public function run()
     {
-        return __DIR__;
+        return dirname(__FILE__);
     }
 }
```

<br>

## DowngradeAbstractPrivateMethodInTraitRector

Remove "abstract" from private methods in traits and adds an empty function body

- class: [`Rector\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector`](../rules/DowngradePhp80/Rector/ClassMethod/DowngradeAbstractPrivateMethodInTraitRector.php)

```diff
 trait SomeTrait
 {
-    abstract private function someAbstractPrivateFunction();
+    private function someAbstractPrivateFunction() {}
 }
```

<br>

## DowngradeAnonymousClassRector

Remove anonymous class

- class: [`Rector\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector`](../rules/DowngradePhp70/Rector/New_/DowngradeAnonymousClassRector.php)

```diff
+class Anonymous
+{
+    public function execute()
+    {
+    }
+}
 class SomeClass
 {
     public function run()
     {
-        return new class {
-            public function execute()
-            {
-            }
-        };
+        return new Anonymous();
     }
 }
```

<br>

## DowngradeArbitraryExpressionArgsToEmptyAndIssetRector

Downgrade arbitrary expression arguments to `empty()` and `isset()`

- class: [`Rector\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector`](../rules/DowngradePhp55/Rector/Isset_/DowngradeArbitraryExpressionArgsToEmptyAndIssetRector.php)

```diff
-if (isset(some_function())) {
+if (some_function() !== null) {
     // ...
 }
```

<br>

## DowngradeArbitraryExpressionsSupportRector

Replace arbitrary expressions used with new or instanceof.

- class: [`Rector\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector`](../rules/DowngradePhp80/Rector/New_/DowngradeArbitraryExpressionsSupportRector.php)

```diff
 function getObjectClassName() {
     return stdClass::class;
 }

-$object = new (getObjectClassName());
+$className = getObjectClassName();
+$object = new $className();
```

<br>

## DowngradeArgumentUnpackingRector

Replace argument unpacking by `call_user_func_array()`

- class: [`Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector`](../rules/DowngradePhp56/Rector/CallLike/DowngradeArgumentUnpackingRector.php)

```diff
 class SomeClass
 {
     public function run(array $items)
     {
-        some_function(...$items);
+        call_user_func_array('some_function', $items);
     }
 }
```

<br>

## DowngradeArrayFilterNullableCallbackRector

Unset nullable callback on array_filter

- class: [`Rector\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector`](../rules/DowngradePhp80/Rector/FuncCall/DowngradeArrayFilterNullableCallbackRector.php)

```diff
 class SomeClass
 {
     public function run($callback = null)
     {
         $data = [[]];
-        var_dump(array_filter($data, null));
+        var_dump(array_filter($data));
     }
 }
```

<br>

## DowngradeArrayFilterUseConstantRector

Replace use ARRAY_FILTER_USE_BOTH and ARRAY_FILTER_USE_KEY to loop to filter it

- class: [`Rector\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector`](../rules/DowngradePhp56/Rector/FuncCall/DowngradeArrayFilterUseConstantRector.php)

```diff
 $arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];

-var_dump(array_filter($arr, function($v, $k) {
-    return $k == 'b' || $v == 4;
-}, ARRAY_FILTER_USE_BOTH));
+$result = [];
+foreach ($arr as $k => $v) {
+    if ($v === 4 || $k === 'b') {
+        $result[$k] = $v;
+    }
+}
+
+var_dump($result);
```

<br>

## DowngradeArrayIsListRector

Replace `array_is_list()` function

- class: [`Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector`](../rules/DowngradePhp81/Rector/FuncCall/DowngradeArrayIsListRector.php)

```diff
-array_is_list([1 => 'apple', 'orange']);
+$arrayIsList = function (array $array) : bool {
+    if (function_exists('array_is_list')) {
+        return array_is_list($array);
+    }
+    if ($array === []) {
+        return true;
+    }
+    $current_key = 0;
+    foreach ($array as $key => $noop) {
+        if ($key !== $current_key) {
+            return false;
+        }
+        ++$current_key;
+    }
+    return true;
+};
+$arrayIsList([1 => 'apple', 'orange']);
```

<br>

## DowngradeArrayKeyFirstLastRector

Downgrade `array_key_first()` and `array_key_last()` functions

- class: [`Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector`](../rules/DowngradePhp73/Rector/FuncCall/DowngradeArrayKeyFirstLastRector.php)

```diff
 class SomeClass
 {
     public function run($items)
     {
-        $firstItemKey = array_key_first($items);
+        reset($items);
+        $firstItemKey = key($items);
     }
 }
```

<br>

## DowngradeArrayMergeCallWithoutArgumentsRector

Add missing param to `array_merge` and `array_merge_recursive`

- class: [`Rector\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector`](../rules/DowngradePhp74/Rector/FuncCall/DowngradeArrayMergeCallWithoutArgumentsRector.php)

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

## DowngradeArraySpreadRector

Replace array spread with array_merge function

- class: [`Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector`](../rules/DowngradePhp74/Rector/Array_/DowngradeArraySpreadRector.php)

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

## DowngradeArraySpreadStringKeyRector

Replace array spread with string key to array_merge function

- class: [`Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector`](../rules/DowngradePhp81/Rector/Array_/DowngradeArraySpreadStringKeyRector.php)

```diff
 $parts = ['a' => 'b'];
 $parts2 = ['c' => 'd'];

-$result = [...$parts, ...$parts2];
+$result = array_merge($parts, $parts2);
```

<br>

## DowngradeAttributeToAnnotationRector

Refactor PHP attribute markers to annotations notation

:wrench: **configure it!**

- class: [`Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector`](../rules/DowngradePhp80/Rector/Class_/DowngradeAttributeToAnnotationRector.php)

```php
use Rector\Config\RectorConfig;
use Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector;
use Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(
        DowngradeAttributeToAnnotationRector::class,
        [new DowngradeAttributeToAnnotation('Symfony\Component\Routing\Annotation\Route')]
    );
};
```

↓

```diff
 use Symfony\Component\Routing\Annotation\Route;

 class SymfonyRoute
 {
-    #[Route(path: '/path', name: 'action')]
+    /**
+     * @Route("/path", name="action")
+     */
     public function action()
     {
     }
 }
```

<br>

## DowngradeBinaryNotationRector

Downgrade binary notation for integers

- class: [`Rector\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector`](../rules/DowngradePhp54/Rector/LNumber/DowngradeBinaryNotationRector.php)

```diff
-$a = 0b11111100101;
+$a = 2021;
```

<br>

## DowngradeBoolvalRector

Replace `boolval()` by type casting to boolean

- class: [`Rector\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector`](../rules/DowngradePhp55/Rector/FuncCall/DowngradeBoolvalRector.php)

```diff
-$bool = boolval($value);
+$bool = (bool) $value;
```

<br>

## DowngradeCallableTypeDeclarationRector

Remove the "callable" param type, add a `@param` tag instead

- class: [`Rector\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector`](../rules/DowngradePhp54/Rector/FunctionLike/DowngradeCallableTypeDeclarationRector.php)

```diff
 class SomeClass
 {
-    public function someFunction(callable $callback)
+    /**
+     * @param callable $callback
+     */
+    public function someFunction($callback)
     {
     }
 }
```

<br>

## DowngradeCatchThrowableRector

Make catch clauses catching `Throwable` also catch `Exception` to support exception hierarchies in PHP 5.

- class: [`Rector\DowngradePhp70\Rector\TryCatch\DowngradeCatchThrowableRector`](../rules/DowngradePhp70/Rector/TryCatch/DowngradeCatchThrowableRector.php)

```diff
 try {
     // Some code...
 } catch (\Throwable $exception) {
     handle();
+} catch (\Exception $exception) {
+    handle();
 }
```

<br>

## DowngradeClassConstantToStringRector

Replace <class>::class constant by string class names

- class: [`Rector\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector`](../rules/DowngradePhp55/Rector/ClassConstFetch/DowngradeClassConstantToStringRector.php)

```diff
 class AnotherClass
 {
 }
 class SomeClass
 {
     public function run()
     {
-        return \AnotherClass::class;
+        return 'AnotherClass';
     }
 }
```

<br>

## DowngradeClassConstantVisibilityRector

Downgrade class constant visibility

- class: [`Rector\DowngradePhp71\Rector\ClassConst\DowngradeClassConstantVisibilityRector`](../rules/DowngradePhp71/Rector/ClassConst/DowngradeClassConstantVisibilityRector.php)

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

## DowngradeClassOnObjectToGetClassRector

Change `$object::class` to get_class($object)

- class: [`Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector`](../rules/DowngradePhp80/Rector/ClassConstFetch/DowngradeClassOnObjectToGetClassRector.php)

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

## DowngradeClosureCallRector

Replace `Closure::call()` by `Closure::bindTo()`

- class: [`Rector\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector`](../rules/DowngradePhp70/Rector/MethodCall/DowngradeClosureCallRector.php)

```diff
-$closure->call($newObj, ...$args);
+call_user_func($closure->bindTo($newObj, $newObj), ...$args);
```

<br>

## DowngradeClosureFromCallableRector

Converts `Closure::fromCallable()` to compatible alternative.

- class: [`Rector\DowngradePhp71\Rector\StaticCall\DowngradeClosureFromCallableRector`](../rules/DowngradePhp71/Rector/StaticCall/DowngradeClosureFromCallableRector.php)

```diff
-\Closure::fromCallable('callable');
+$callable = 'callable';
+function () use ($callable) {
+    return $callable(...func_get_args());
+};
```

<br>

## DowngradeContravariantArgumentTypeRector

Remove contravariant argument type declarations

- class: [`Rector\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector`](../rules/DowngradePhp74/Rector/ClassMethod/DowngradeContravariantArgumentTypeRector.php)

```diff
 class ParentType {}
 class ChildType extends ParentType {}

 class A
 {
     public function contraVariantArguments(ChildType $type)
     {
     }
 }

 class B extends A
 {
-    public function contraVariantArguments(ParentType $type)
+    /**
+     * @param ParentType $type
+     */
+    public function contraVariantArguments($type)
     {
     }
 }
```

<br>

## DowngradeCovariantReturnTypeRector

Make method return same type as parent

- class: [`Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector`](../rules/DowngradePhp74/Rector/ClassMethod/DowngradeCovariantReturnTypeRector.php)

```diff
 class ParentType {}
 class ChildType extends ParentType {}

 class A
 {
     public function covariantReturnTypes(): ParentType
     {
     }
 }

 class B extends A
 {
-    public function covariantReturnTypes(): ChildType
+    /**
+     * @return ChildType
+     */
+    public function covariantReturnTypes(): ParentType
     {
     }
 }
```

<br>

## DowngradeDefineArrayConstantRector

Change array contant definition via define to const

- class: [`Rector\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector`](../rules/DowngradePhp70/Rector/Expression/DowngradeDefineArrayConstantRector.php)

```diff
-define('ANIMALS', [
+const ANIMALS = [
     'dog',
     'cat',
     'bird'
-]);
+];
```

<br>

## DowngradeDereferenceableOperationRector

Add parentheses around non-dereferenceable expressions.

- class: [`Rector\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector`](../rules/DowngradePhp80/Rector/ArrayDimFetch/DowngradeDereferenceableOperationRector.php)

```diff
 function getFirstChar(string $str, string $suffix = '')
 {
-    return "$str$suffix"[0];
+    return ("$str$suffix")[0];
 }
```

<br>

## DowngradeDirnameLevelsRector

Replace the 2nd argument of `dirname()`

- class: [`Rector\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector`](../rules/DowngradePhp70/Rector/FuncCall/DowngradeDirnameLevelsRector.php)

```diff
-return dirname($path, 2);
+return dirname(dirname($path));
```

<br>

## DowngradeEnumExistsRector

Replace `enum_exists()` function

-
class: [`Rector\DowngradePhp81\Rector\FuncCall\DowngradeEnumExistsRector`](../rules/DowngradePhp81/Rector/FuncCall/DowngradeEnumExistsRector.php)

```diff
-enum_exists('SomeEnum', true);
+$enumExists = function (string $enum, bool $autoload = true) : bool {
+    if (function_exists('enum_exists')) {
+        return enum_exists($enum, $autoload);
+    }
+    return $autoload && class_exists($enum) && false;
+};
+$enumExists('SomeEnum', true);
```

<br>

## DowngradeEnumToConstantListClassRector

Downgrade enum to constant list class

- class: [`Rector\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector`](../rules/DowngradePhp80/Rector/Enum_/DowngradeEnumToConstantListClassRector.php)

```diff
-enum Direction
+class Direction
 {
-    case LEFT;
+    public const LEFT = 'left';

-    case RIGHT;
+    public const RIGHT = 'right';
 }
```

<br>

## DowngradeExponentialAssignmentOperatorRector

Remove exponential assignment operator **=

- class: [`Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector`](../rules/DowngradePhp56/Rector/Pow/DowngradeExponentialAssignmentOperatorRector.php)

```diff
-$a **= 3;
+$a = pow($a, 3);
```

<br>

## DowngradeExponentialOperatorRector

Changes ** (exp) operator to pow(val, val2)

- class: [`Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector`](../rules/DowngradePhp56/Rector/Pow/DowngradeExponentialOperatorRector.php)

```diff
-1**2;
+pow(1, 2);
```

<br>

## DowngradeFinalizePublicClassConstantRector

Remove final from class constants

- class: [`Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector`](../rules/DowngradePhp81/Rector/ClassConst/DowngradeFinalizePublicClassConstantRector.php)

```diff
 class SomeClass
 {
-    final public const NAME = 'value';
+    public const NAME = 'value';
 }
```

<br>

## DowngradeFirstClassCallableSyntaxRector

Replace variadic placeholders usage by `Closure::fromCallable()`

- class: [`Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector`](../rules/DowngradePhp81/Rector/FuncCall/DowngradeFirstClassCallableSyntaxRector.php)

```diff
-$cb = strlen(...);
+$cb = \Closure::fromCallable('strlen');
```

<br>

## DowngradeFlexibleHeredocSyntaxRector

Remove indentation from heredoc/nowdoc

- class: [`Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector`](../rules/DowngradePhp73/Rector/String_/DowngradeFlexibleHeredocSyntaxRector.php)

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

## DowngradeForeachListRector

Downgrade `list()` support in foreach constructs

- class: [`Rector\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector`](../rules/DowngradePhp55/Rector/Foreach_/DowngradeForeachListRector.php)

```diff
-foreach ($array as $key => list($item1, $item2)) {
+foreach ($array as $key => arrayItem) {
+    list($item1, $item2) = $arrayItem;
     var_dump($item1, $item2);
 }
```

<br>

## DowngradeFreadFwriteFalsyToNegationRector

Changes `fread()` or `fwrite()` compare to false to negation check

- class: [`Rector\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector`](../rules/DowngradePhp74/Rector/Identical/DowngradeFreadFwriteFalsyToNegationRector.php)

```diff
-fread($handle, $length) === false;
-fwrite($fp, '1') === false;
+!fread($handle, $length);
+!fwrite($fp, '1');
```

<br>

## DowngradeIndirectCallByArrayRector

Downgrade indirect method call by array variable

- class: [`Rector\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector`](../rules/DowngradePhp54/Rector/FuncCall/DowngradeIndirectCallByArrayRector.php)

```diff
 class Hello {
     public static function world($x) {
         echo "Hello, $x\n";
     }
 }

 $func = array('Hello','world');
-$func('you');
+call_user_func($func, 'you');
```

<br>

## DowngradeInstanceMethodCallRector

Downgrade instance and method call/property access

- class: [`Rector\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector`](../rules/DowngradePhp54/Rector/MethodCall/DowngradeInstanceMethodCallRector.php)

```diff
-echo (new \ReflectionClass('\\stdClass'))->getName();
+$object = new \ReflectionClass('\\stdClass');
+echo $object->getName();
```

<br>

## DowngradeInstanceofThrowableRector

Add `instanceof Exception` check as a fallback to `instanceof Throwable` to support exception hierarchies in PHP 5

- class: [`Rector\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector`](../rules/DowngradePhp70/Rector/Instanceof_/DowngradeInstanceofThrowableRector.php)

```diff
-return $e instanceof \Throwable;
+return ($throwable = $e) instanceof \Throwable || $throwable instanceof \Exception;
```

<br>

## DowngradeIsCountableRector

Downgrade `is_countable()` to former version

- class: [`Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector`](../rules/DowngradePhp73/Rector/FuncCall/DowngradeIsCountableRector.php)

```diff
 $items = [];
-return is_countable($items);
+return is_array($items) || $items instanceof Countable;
```

<br>

## DowngradeIsIterableRector

Change is_iterable with array and Traversable object type check

- class: [`Rector\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector`](../rules/DowngradePhp71/Rector/FuncCall/DowngradeIsIterableRector.php)

```diff
 class SomeClass
 {
     public function run($obj)
     {
-        is_iterable($obj);
+        is_array($obj) || $obj instanceof \Traversable;
     }
 }
```

<br>

## DowngradeIterablePseudoTypeDeclarationRector

Remove the iterable pseudo type params and returns, add `@param` and `@return` tags instead

- class: [`Rector\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector`](../rules/DowngradePhp71/Rector/FunctionLike/DowngradeIterablePseudoTypeDeclarationRector.php)

```diff
 class SomeClass
 {
-    public function run(iterable $iterator): iterable
+    /**
+     * @param mixed[]|\Traversable $iterator
+     * @return mixed[]|\Traversable
+     */
+    public function run($iterator)
     {
         // do something
     }
 }
```

<br>

## DowngradeJsonDecodeNullAssociativeArgRector

Downgrade `json_decode()` with null associative argument function

- class: [`Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector`](../rules/DowngradePhp72/Rector/FuncCall/DowngradeJsonDecodeNullAssociativeArgRector.php)

```diff
 function exactlyNull(string $json)
 {
-    $value = json_decode($json, null);
+    $value = json_decode($json, true);
 }

 function possiblyNull(string $json, ?bool $associative)
 {
-    $value = json_decode($json, $associative);
+    $value = json_decode($json, $associative === null ?: $associative);
 }
```

<br>

## DowngradeKeysInListRector

Extract keys in list to its own variable assignment

- class: [`Rector\DowngradePhp71\Rector\List_\DowngradeKeysInListRector`](../rules/DowngradePhp71/Rector/List_/DowngradeKeysInListRector.php)

```diff
 class SomeClass
 {
     public function run(): void
     {
         $data = [
             ["id" => 1, "name" => 'Tom'],
             ["id" => 2, "name" => 'Fred'],
         ];
-        list("id" => $id1, "name" => $name1) = $data[0];
+        $id1 = $data[0]["id"];
+        $name1 = $data[0]["name"];
     }
 }
```

<br>

## DowngradeListReferenceAssignmentRector

Convert the list reference assignment to its equivalent PHP 7.2 code

- class: [`Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector`](../rules/DowngradePhp73/Rector/List_/DowngradeListReferenceAssignmentRector.php)

```diff
 class SomeClass
 {
     public function run($string)
     {
         $array = [1, 2, 3];
-        list($a, &$b) = $array;
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

## DowngradeMatchToSwitchRector

Downgrade `match()` to `switch()`

- class: [`Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector`](../rules/DowngradePhp80/Rector/Expression/DowngradeMatchToSwitchRector.php)

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

## DowngradeMethodCallOnCloneRector

Replace (clone `$obj)->call()` to object assign and call

- class: [`Rector\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector`](../rules/DowngradePhp70/Rector/MethodCall/DowngradeMethodCallOnCloneRector.php)

```diff
-(clone $this)->execute();
+$object = (clone $this);
+$object->execute();
```

<br>

## DowngradeMixedTypeDeclarationRector

Remove the "mixed" param and return type, add a `@param` and `@return` tag instead

- class: [`Rector\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector`](../rules/DowngradePhp80/Rector/FunctionLike/DowngradeMixedTypeDeclarationRector.php)

```diff
 class SomeClass
 {
-    public function someFunction(mixed $anything): mixed
+    /**
+     * @param mixed $anything
+     * @return mixed
+     */
+    public function someFunction($anything)
     {
     }
 }
```

<br>

## DowngradeMixedTypeTypedPropertyRector

Removes mixed type property type definition, adding `@var` annotations instead.

- class: [`Rector\DowngradePhp80\Rector\Property\DowngradeMixedTypeTypedPropertyRector`](../rules/DowngradePhp80/Rector/Property/DowngradeMixedTypeTypedPropertyRector.php)

```diff
 class SomeClass
 {
-    private mixed $property;
+    /**
+     * @var mixed
+     */
+    private $property;
 }
```

<br>

## DowngradeNamedArgumentRector

Remove named argument

- class: [`Rector\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector`](../rules/DowngradePhp80/Rector/MethodCall/DowngradeNamedArgumentRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        $this->execute(b: 100);
+        $this->execute(null, 100);
     }

     private function execute($a = null, $b = null)
     {
     }
 }
```

<br>

## DowngradeNegativeStringOffsetToStrlenRector

Downgrade negative string offset to strlen

- class: [`Rector\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector`](../rules/DowngradePhp71/Rector/String_/DowngradeNegativeStringOffsetToStrlenRector.php)

```diff
-echo 'abcdef'[-2];
-echo strpos('aabbcc', 'b', -3);
-echo strpos($var, 'b', -3);
+echo 'abcdef'[strlen('abcdef') - 2];
+echo strpos('aabbcc', 'b', strlen('aabbcc') - 3);
+echo strpos($var, 'b', strlen($var) - 3);
```

<br>

## DowngradeNeverTypeDeclarationRector

Remove "never" return type, add a `"@return` never" tag instead

- class: [`Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector`](../rules/DowngradePhp81/Rector/FunctionLike/DowngradeNeverTypeDeclarationRector.php)

```diff
-function someFunction(): never
+/**
+ * @return never
+ */
+function someFunction()
 {
 }
```

<br>

## DowngradeNewInInitializerRector

Replace New in initializers

- class: [`Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector`](../rules/DowngradePhp81/Rector/FunctionLike/DowngradeNewInInitializerRector.php)

```diff
 class SomeClass
 {
     public function __construct(
-        private Logger $logger = new NullLogger,
+        private ?Logger $logger = null,
     ) {
+        $this->logger = $logger ?? new NullLogger;
     }
 }
```

<br>

## DowngradeNonCapturingCatchesRector

Downgrade catch () without variable to one

- class: [`Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector`](../rules/DowngradePhp80/Rector/Catch_/DowngradeNonCapturingCatchesRector.php)

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

## DowngradeNullCoalesceRector

Change null coalesce to isset ternary check

- class: [`Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector`](../rules/DowngradePhp70/Rector/Coalesce/DowngradeNullCoalesceRector.php)

```diff
-$username = $_GET['user'] ?? 'nobody';
+$username = isset($_GET['user']) ? $_GET['user'] : 'nobody';
```

<br>

## DowngradeNullCoalescingOperatorRector

Remove null coalescing operator ??=

- class: [`Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector`](../rules/DowngradePhp74/Rector/Coalesce/DowngradeNullCoalescingOperatorRector.php)

```diff
 $array = [];
-$array['user_id'] ??= 'value';
+$array['user_id'] = $array['user_id'] ?? 'value';
```

<br>

## DowngradeNullableTypeDeclarationRector

Remove the nullable type params, add `@param` tags instead

- class: [`Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector`](../rules/DowngradePhp71/Rector/FunctionLike/DowngradeNullableTypeDeclarationRector.php)

```diff
 class SomeClass
 {
-    public function run(?string $input): ?string
+    /**
+     * @param string|null $input
+     * @return string|null
+     */
+    public function run($input)
     {
     }
 }
```

<br>

## DowngradeNullsafeToTernaryOperatorRector

Change nullsafe operator to ternary operator rector

- class: [`Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector`](../rules/DowngradePhp80/Rector/NullsafeMethodCall/DowngradeNullsafeToTernaryOperatorRector.php)

```diff
-$dateAsString = $booking->getStartDate()?->asDateTimeString();
-$dateAsString = $booking->startDate?->dateTimeString;
+$dateAsString = ($bookingGetStartDate = $booking->getStartDate()) ? $bookingGetStartDate->asDateTimeString() : null;
+$dateAsString = ($bookingGetStartDate = $booking->startDate) ? $bookingGetStartDate->dateTimeString : null;
```

<br>

## DowngradeNumberFormatNoFourthArgRector

Downgrade number_format arg to fill 4th arg when only 3rd arg filled

- class: [`Rector\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector`](../rules/DowngradePhp80/Rector/FuncCall/DowngradeNumberFormatNoFourthArgRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        return number_format(1000, 2, ',');
+        return number_format(1000, 2, ',', ',');
     }
 }
```

<br>

## DowngradeNumericLiteralSeparatorRector

Remove "_" as thousands separator in numbers

- class: [`Rector\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector`](../rules/DowngradePhp74/Rector/LNumber/DowngradeNumericLiteralSeparatorRector.php)

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

## DowngradeObjectTypeDeclarationRector

Remove the "object" param and return type, add a `@param` and `@return` tags instead

- class: [`Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector`](../rules/DowngradePhp72/Rector/FunctionLike/DowngradeObjectTypeDeclarationRector.php)

```diff
 class SomeClass
 {
-    public function someFunction(object $someObject): object
+    /**
+     * @param object $someObject
+     * @return object
+     */
+    public function someFunction($someObject)
     {
     }
 }
```

<br>

## DowngradeParameterTypeWideningRector

Change param type to match the lowest type in whole family tree

:wrench: **configure it!**

- class: [`Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector`](../rules/DowngradePhp72/Rector/ClassMethod/DowngradeParameterTypeWideningRector.php)

```php
use Rector\Config\RectorConfig;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(DowngradeParameterTypeWideningRector::class, [
        'ContainerInterface' => ['set', 'get', 'has', 'initialized'],
        'SomeContainerInterface' => ['set', 'has'],
    ]);
};
```

↓

```diff
 interface SomeInterface
 {
-    public function test(array $input);
+    /**
+     * @param mixed[] $input
+     */
+    public function test($input);
 }

 final class SomeClass implements SomeInterface
 {
     public function test($input)
     {
     }
 }
```

<br>

## DowngradeParentTypeDeclarationRector

Remove "parent" return type, add a `"@return` parent" tag instead

- class: [`Rector\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector`](../rules/DowngradePhp70/Rector/ClassMethod/DowngradeParentTypeDeclarationRector.php)

```diff
 class ParentClass
 {
 }

 class SomeClass extends ParentClass
 {
-    public function foo(): parent
+    /**
+     * @return parent
+     */
+    public function foo()
     {
         return $this;
     }
 }
```

<br>

## DowngradePhp71JsonConstRector

Remove Json constant that available only in php 7.1

- class: [`Rector\DowngradePhp71\Rector\ConstFetch\DowngradePhp71JsonConstRector`](../rules/DowngradePhp71/Rector/ConstFetch/DowngradePhp71JsonConstRector.php)

```diff
-json_encode($content, JSON_UNESCAPED_LINE_TERMINATORS);
+json_encode($content, 0);
```

<br>

## DowngradePhp72JsonConstRector

Remove Json constant that available only in php 7.2

- class: [`Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector`](../rules/DowngradePhp72/Rector/ConstFetch/DowngradePhp72JsonConstRector.php)

```diff
-$inDecoder = new Decoder($connection, true, 512, \JSON_INVALID_UTF8_IGNORE);
-$inDecoder = new Decoder($connection, true, 512, \JSON_INVALID_UTF8_SUBSTITUTE);
+$inDecoder = new Decoder($connection, true, 512, 0);
+$inDecoder = new Decoder($connection, true, 512, 0);
```

<br>

## DowngradePhp73JsonConstRector

Remove Json constant that available only in php 7.3

- class: [`Rector\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector`](../rules/DowngradePhp73/Rector/ConstFetch/DowngradePhp73JsonConstRector.php)

```diff
-json_encode($content, JSON_THROW_ON_ERROR);
+json_encode($content, 0);
```

<br>

## DowngradePhp80ResourceReturnToObjectRector

change instanceof Object to is_resource

- class: [`Rector\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector`](../rules/DowngradePhp80/Rector/Instanceof_/DowngradePhp80ResourceReturnToObjectRector.php)

```diff
 class SomeClass
 {
     public function run($obj)
     {
-        $obj instanceof \CurlHandle;
+        is_resource($obj) || $obj instanceof \CurlHandle;
     }
 }
```

<br>

## DowngradePhp81ResourceReturnToObjectRector

change instanceof Object to is_resource

- class: [`Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector`](../rules/DowngradePhp81/Rector/Instanceof_/DowngradePhp81ResourceReturnToObjectRector.php)

```diff
 class SomeClass
 {
     public function run($obj)
     {
-        $obj instanceof \finfo;
+        is_resource($obj) || $obj instanceof \finfo;
     }
 }
```

<br>

## DowngradePhpTokenRector

`"something()"` will be renamed to `"somethingElse()"`

- class: [`Rector\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector`](../rules/DowngradePhp80/Rector/StaticCall/DowngradePhpTokenRector.php)

```diff
-$tokens = \PhpToken::tokenize($code);
+$tokens = token_get_all($code);

-foreach ($tokens as $phpToken) {
-   $name = $phpToken->getTokenName();
-   $text = $phpToken->text;
+foreach ($tokens as $token) {
+    $name = is_array($token) ? token_name($token[0]) : null;
+    $text = is_array($token) ? $token[1] : $token;
 }
```

<br>

## DowngradePipeToMultiCatchExceptionRector

Downgrade single one | separated to multi catch exception

- class: [`Rector\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector`](../rules/DowngradePhp71/Rector/TryCatch/DowngradePipeToMultiCatchExceptionRector.php)

```diff
 try {
     // Some code...
-} catch (ExceptionType1 | ExceptionType2 $exception) {
+} catch (ExceptionType1 $exception) {
+    $sameCode;
+} catch (ExceptionType2 $exception) {
     $sameCode;
 }
```

<br>

## DowngradePregUnmatchedAsNullConstantRector

Remove PREG_UNMATCHED_AS_NULL from preg_match and set null value on empty string matched on each match

- class: [`Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector`](../rules/DowngradePhp72/Rector/FuncCall/DowngradePregUnmatchedAsNullConstantRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_UNMATCHED_AS_NULL);
+        preg_match('/(a)(b)*(c)/', 'ac', $matches);
+        array_walk_recursive($matches, function (&$value) {
+            if ($value === '') {
+                $value = null;
+            }
+        });
     }
 }
```

<br>

## DowngradePreviouslyImplementedInterfaceRector

Downgrade previously implemented interface

- class: [`Rector\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector`](../rules/DowngradePhp74/Rector/Interface_/DowngradePreviouslyImplementedInterfaceRector.php)

```diff
 interface ContainerExceptionInterface extends Throwable
 {
 }

-interface ExceptionInterface extends ContainerExceptionInterface, Throwable
+interface ExceptionInterface extends ContainerExceptionInterface
 {
 }
```

<br>

## DowngradePropertyPromotionRector

Change constructor property promotion to property assign

- class: [`Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector`](../rules/DowngradePhp80/Rector/Class_/DowngradePropertyPromotionRector.php)

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

## DowngradePureIntersectionTypeRector

Remove the intersection type params and returns, add `@param/@return` tags instead

- class: [`Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector`](../rules/DowngradePhp81/Rector/FunctionLike/DowngradePureIntersectionTypeRector.php)

```diff
-function someFunction(): Foo&Bar
+/**
+ * @return Foo&Bar
+ */
+function someFunction()
 {
 }
```

<br>

## DowngradeReadonlyClassRector

Remove "readonly" class type, decorate all properties to "readonly"

- class: [`Rector\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector`](../rules/DowngradePhp82/Rector/Class_/DowngradeReadonlyClassRector.php)

```diff
-final readonly class SomeClass
+final class SomeClass
 {
-    public string $foo;
+    public readonly string $foo;

     public function __construct()
     {
         $this->foo = 'foo';
     }
 }
```

<br>

## DowngradeReadonlyPropertyRector

Remove "readonly" property type, add a "@readonly" tag instead

- class: [`Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector`](../rules/DowngradePhp81/Rector/Property/DowngradeReadonlyPropertyRector.php)

```diff
 class SomeClass
 {
-    public readonly string $foo;
+    /**
+     * @readonly
+     */
+    public string $foo;

     public function __construct()
     {
         $this->foo = 'foo';
     }
 }
```

<br>

## DowngradeRecursiveDirectoryIteratorHasChildrenRector

Remove bool type hint on child of RecursiveDirectoryIterator hasChildren allowLinks parameter

- class: [`Rector\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector`](../rules/DowngradePhp80/Rector/ClassMethod/DowngradeRecursiveDirectoryIteratorHasChildrenRector.php)

```diff
 class RecursiveDirectoryIteratorChild extends \RecursiveDirectoryIterator
 {
-    public function hasChildren(bool $allowLinks = false): bool
+    public function hasChildren($allowLinks = false): bool
     {
         return true;
     }
 }
```

<br>

## DowngradeReflectionClassGetConstantsFilterRector

Downgrade ReflectionClass->getConstants(ReflectionClassConstant::IS_*)

- class: [`Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector`](../rules/DowngradePhp80/Rector/MethodCall/DowngradeReflectionClassGetConstantsFilterRector.php)

```diff
 $reflectionClass = new ReflectionClass('SomeClass');
-$constants = $reflectionClass->getConstants(ReflectionClassConstant::IS_PUBLIC));
+$reflectionClassConstants = $reflectionClass->getReflectionConstants();
+$result = [];
+array_walk($reflectionClassConstants, function ($value) use (&$result) {
+    if ($value->isPublic()) {
+       $result[$value->getName()] = $value->getValue();
+    }
+});
+$constants = $result;
```

<br>

## DowngradeReflectionGetAttributesRector

Remove reflection `getAttributes()` class method code

- class: [`Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector`](../rules/DowngradePhp80/Rector/MethodCall/DowngradeReflectionGetAttributesRector.php)

```diff
 class SomeClass
 {
     public function run(ReflectionClass $reflectionClass)
     {
-        if ($reflectionClass->getAttributes()) {
+        if ([]) {
             return true;
         }

         return false;
     }
 }
```

<br>

## DowngradeReflectionGetTypeRector

Downgrade reflection `$refleciton->getType()` method call

- class: [`Rector\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector`](../rules/DowngradePhp74/Rector/MethodCall/DowngradeReflectionGetTypeRector.php)

```diff
 class SomeClass
 {
     public function run(ReflectionProperty $reflectionProperty)
     {
-        if ($reflectionProperty->getType()) {
+        if (null) {
             return true;
         }

         return false;
     }
 }
```

<br>

## DowngradeReflectionPropertyGetDefaultValueRector

Downgrade `ReflectionProperty->getDefaultValue()`

- class: [`Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector`](../rules/DowngradePhp80/Rector/MethodCall/DowngradeReflectionPropertyGetDefaultValueRector.php)

```diff
 class SomeClass
 {
     public function run(ReflectionProperty $reflectionProperty)
     {
-        return $reflectionProperty->getDefaultValue();
+        return $reflectionProperty->getDeclaringClass()->getDefaultProperties()[$reflectionProperty->getName()] ?? null;
     }
 }
```

<br>

## DowngradeScalarTypeDeclarationRector

Remove the type params and return type, add `@param` and `@return` tags instead

- class: [`Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector`](../rules/DowngradePhp70/Rector/FunctionLike/DowngradeScalarTypeDeclarationRector.php)

```diff
 class SomeClass
 {
-    public function run(string $input): string
+    /**
+     * @param string $input
+     * @return string
+     */
+    public function run($input)
     {
     }
 }
```

<br>

## DowngradeSelfTypeDeclarationRector

Remove "self" return type, add a `"@return` `$this"` tag instead

- class: [`Rector\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector`](../rules/DowngradePhp70/Rector/ClassMethod/DowngradeSelfTypeDeclarationRector.php)

```diff
 class SomeClass
 {
-    public function foo(): self
+    /**
+     * @return $this
+     */
+    public function foo()
     {
         return $this;
     }
 }
```

<br>

## DowngradeSessionStartArrayOptionsRector

Move array option of session_start($options) to before statement's `ini_set()`

- class: [`Rector\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector`](../rules/DowngradePhp70/Rector/FuncCall/DowngradeSessionStartArrayOptionsRector.php)

```diff
-session_start([
-    'cache_limiter' => 'private',
-]);
+ini_set('session.cache_limiter', 'private');
+session_start();
```

<br>

## DowngradeSpaceshipRector

Change spaceship with check equal, and ternary to result 0, -1, 1

- class: [`Rector\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector`](../rules/DowngradePhp70/Rector/Spaceship/DowngradeSpaceshipRector.php)

```diff
-return $a <=> $b;
+$battleShipcompare = function ($left, $right) {
+    if ($left === $right) {
+        return 0;
+    }
+    return $left < $right ? -1 : 1;
+};
+return $battleShipcompare($a, $b);
```

<br>

## DowngradeStaticClosureRector

Remove static from closure

- class: [`Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector`](../rules/DowngradePhp54/Rector/Closure/DowngradeStaticClosureRector.php)

```diff
 final class SomeClass
 {
     public function run()
     {
-        return static function () {
+        return function () {
             return true;
         };
     }
 }
```

<br>

## DowngradeStaticTypeDeclarationRector

Remove "static" return and param type, add a `"@param` `$this"` and `"@return` `$this"` tag instead

- class: [`Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector`](../rules/DowngradePhp80/Rector/ClassMethod/DowngradeStaticTypeDeclarationRector.php)

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

## DowngradeStrContainsRector

Replace `str_contains()` with `strpos()` !== false

- class: [`Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector`](../rules/DowngradePhp80/Rector/FuncCall/DowngradeStrContainsRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        return str_contains('abc', 'a');
+        return strpos('abc', 'a') !== false;
     }
 }
```

<br>

## DowngradeStrEndsWithRector

Downgrade `str_ends_with()` to `strncmp()` version

- class: [`Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector`](../rules/DowngradePhp80/Rector/FuncCall/DowngradeStrEndsWithRector.php)

```diff
-str_ends_with($haystack, $needle);
+"" === $needle || ("" !== $haystack && 0 === substr_compare($haystack, $needle, -\strlen($needle)));
```

<br>

## DowngradeStrStartsWithRector

Downgrade `str_starts_with()` to `strncmp()` version

- class: [`Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector`](../rules/DowngradePhp80/Rector/FuncCall/DowngradeStrStartsWithRector.php)

```diff
-str_starts_with($haystack, $needle);
+strncmp($haystack, $needle, strlen($needle)) === 0;
```

<br>

## DowngradeStreamIsattyRector

Downgrade `stream_isatty()` function

- class: [`Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector`](../rules/DowngradePhp72/Rector/FuncCall/DowngradeStreamIsattyRector.php)

```diff
 class SomeClass
 {
     public function run($stream)
     {
-        $isStream = stream_isatty($stream);
+        $streamIsatty = function ($stream) {
+            if (\function_exists('stream_isatty')) {
+                return stream_isatty($stream);
+            }
+
+            if (!\is_resource($stream)) {
+                trigger_error('stream_isatty() expects parameter 1 to be resource, '.\gettype($stream).' given', \E_USER_WARNING);
+
+                return false;
+            }
+
+            if ('\\' === \DIRECTORY_SEPARATOR) {
+                $stat = @fstat($stream);
+                // Check if formatted mode is S_IFCHR
+                return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
+            }
+
+            return \function_exists('posix_isatty') && @posix_isatty($stream);
+        };
+        $isStream = $streamIsatty($stream);
     }
 }
```

<br>

## DowngradeStrictTypeDeclarationRector

Remove the declare(strict_types=1)

- class: [`Rector\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector`](../rules/DowngradePhp70/Rector/Declare_/DowngradeStrictTypeDeclarationRector.php)

```diff
-declare(strict_types=1);
 echo 'something';
```

<br>

## DowngradeStringReturnTypeOnToStringRector

Add "string" return on current `__toString()` method when parent method has string return on `__toString()` method

- class: [`Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector`](../rules/DowngradePhp80/Rector/ClassMethod/DowngradeStringReturnTypeOnToStringRector.php)

```diff
 abstract class ParentClass
 {
     public function __toString(): string
     {
         return 'value';
     }
 }

 class ChildClass extends ParentClass
 {
-    public function __toString()
+    public function __toString(): string
     {
         return 'value';
     }
 }
```

<br>

## DowngradeStripTagsCallWithArrayRector

Convert 2nd param to `strip_tags` from array to string

- class: [`Rector\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector`](../rules/DowngradePhp74/Rector/FuncCall/DowngradeStripTagsCallWithArrayRector.php)

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

## DowngradeThisInClosureRector

Downgrade `$this->` inside Closure to use assigned `$self` = `$this` before Closure

- class: [`Rector\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector`](../rules/DowngradePhp54/Rector/Closure/DowngradeThisInClosureRector.php)

```diff
 class SomeClass
 {
     public $property = 'test';

     public function run()
     {
-        $function = function () {
-            echo $this->property;
+        $self = $this;
+        $function = function () use ($self) {
+            echo $self->property;
         };

         $function();
     }
 }
```

<br>

## DowngradeThrowExprRector

Downgrade throw expression

- class: [`Rector\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector`](../rules/DowngradePhp80/Rector/Expression/DowngradeThrowExprRector.php)

```diff
-echo $variable ?? throw new RuntimeException();
+if (! isset($variable)) {
+    throw new RuntimeException();
+}
+
+echo $variable;
```

<br>

## DowngradeThrowableTypeDeclarationRector

Replace `Throwable` type hints by PHPDoc tags

- class: [`Rector\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector`](../rules/DowngradePhp70/Rector/FunctionLike/DowngradeThrowableTypeDeclarationRector.php)

```diff
 class SomeClass
 {
-    public function foo(\Throwable $e): ?\Throwable
+    /**
+     * @param \Throwable $e
+     * @return \Throwable|null
+     */
+    public function foo($e)
     {
         return new \Exception("Troubles");
     }
 }
```

<br>

## DowngradeTrailingCommasInFunctionCallsRector

Remove trailing commas in function calls

- class: [`Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector`](../rules/DowngradePhp73/Rector/FuncCall/DowngradeTrailingCommasInFunctionCallsRector.php)

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

## DowngradeTrailingCommasInParamUseRector

Remove trailing commas in param or use list

- class: [`Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector`](../rules/DowngradePhp80/Rector/ClassMethod/DowngradeTrailingCommasInParamUseRector.php)

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

## DowngradeTypedPropertyRector

Changes property type definition from type definitions to `@var` annotations.

- class: [`Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector`](../rules/DowngradePhp74/Rector/Property/DowngradeTypedPropertyRector.php)

```diff
 class SomeClass
 {
-    private string $property;
+    /**
+     * @var string
+     */
+    private $property;
 }
```

<br>

## DowngradeUncallableValueCallToCallUserFuncRector

Downgrade calling a value that is not directly callable in PHP 5 (property, static property, closure, …) to call_user_func.

- class: [`Rector\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector`](../rules/DowngradePhp70/Rector/FuncCall/DowngradeUncallableValueCallToCallUserFuncRector.php)

```diff
 final class Foo
 {
     /** @var callable */
     public $handler;
     /** @var callable */
     public static $staticHandler;
 }

 $foo = new Foo;
-($foo->handler)(/* args */);
-($foo::$staticHandler)(41);
+call_user_func($foo->handler, /* args */);
+call_user_func($foo::$staticHandler, 41);

-(function() { /* … */ })();
+call_user_func(function() { /* … */ });
```

<br>

## DowngradeUnionTypeDeclarationRector

Remove the union type params and returns, add `@param/@return` tags instead

- class: [`Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector`](../rules/DowngradePhp80/Rector/FunctionLike/DowngradeUnionTypeDeclarationRector.php)

```diff
 class SomeClass
 {
-    public function echoInput(string|int $input): int|bool
+    /**
+     * @param string|int $input
+     * @return int|bool
+     */
+    public function echoInput($input)
     {
         echo $input;
     }
 }
```

<br>

## DowngradeUnionTypeTypedPropertyRector

Removes union type property type definition, adding `@var` annotations instead.

- class: [`Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector`](../rules/DowngradePhp80/Rector/Property/DowngradeUnionTypeTypedPropertyRector.php)

```diff
 class SomeClass
 {
-    private string|int $property;
+    /**
+     * @var string|int
+     */
+    private $property;
 }
```

<br>

## DowngradeUnnecessarilyParenthesizedExpressionRector

Remove parentheses around expressions allowed by Uniform variable syntax RFC where they are not necessary to prevent parse errors on PHP 5.

- class: [`Rector\DowngradePhp70\Rector\Expr\DowngradeUnnecessarilyParenthesizedExpressionRector`](../rules/DowngradePhp70/Rector/Expr/DowngradeUnnecessarilyParenthesizedExpressionRector.php)

```diff
-($f)['foo'];
-($f)->foo;
-($f)->foo();
-($f)::$foo;
-($f)::foo();
-($f)();
+$f['foo'];
+$f->foo;
+$f->foo();
+$f::$foo;
+$f::foo();
+$f();
```

<br>

## DowngradeUseFunctionRector

Replace imports of functions and constants

- class: [`Rector\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector`](../rules/DowngradePhp56/Rector/Use_/DowngradeUseFunctionRector.php)

```diff
-use function Foo\Bar\baz;
-
-$var = baz();
+$var = \Foo\Bar\baz();
```

<br>

## DowngradeVoidTypeDeclarationRector

Remove "void" return type, add a `"@return` void" tag instead

- class: [`Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector`](../rules/DowngradePhp71/Rector/FunctionLike/DowngradeVoidTypeDeclarationRector.php)

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

## SetCookieOptionsArrayToArgumentsRector

Convert setcookie option array to arguments

- class: [`Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector`](../rules/DowngradePhp73/Rector/FuncCall/SetCookieOptionsArrayToArgumentsRector.php)

```diff
-setcookie('name', $value, ['expires' => 360]);
+setcookie('name', $value, 360);
```

<br>

## ShortArrayToLongArrayRector

Replace short arrays by long arrays

- class: [`Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector`](../rules/DowngradePhp54/Rector/Array_/ShortArrayToLongArrayRector.php)

```diff
-$a = [1, 2, 3];
+$a = array(1, 2, 3);
```

<br>

## SplitGroupedUseImportsRector

Refactor grouped use imports to standalone lines

- class: [`Rector\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector`](../rules/DowngradePhp70/Rector/GroupUse/SplitGroupedUseImportsRector.php)

```diff
-use SomeNamespace\{
-    First,
-    Second
-};
+use SomeNamespace\First;
+use SomeNamespace\Second;
```

<br>

## SymmetricArrayDestructuringToListRector

Downgrade Symmetric array destructuring to `list()` function

- class: [`Rector\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector`](../rules/DowngradePhp71/Rector/Array_/SymmetricArrayDestructuringToListRector.php)

```diff
-[$id1, $name1] = $data;
+list($id1, $name1) = $data;
```

<br>
