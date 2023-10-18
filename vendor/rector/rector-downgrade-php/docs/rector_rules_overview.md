# 71 Rules Overview

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

## DowngradeArbitraryExpressionsSupportRector

Replace arbitrary expressions used with new or instanceof

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

## DowngradeArrayIsListRector

Replace `array_is_list()` function

- class: [`Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector`](../rules/DowngradePhp81/Rector/FuncCall/DowngradeArrayIsListRector.php)

```diff
-array_is_list([1 => 'apple', 'orange']);
+$arrayIsList = function (array $array) : bool {
+    if (function_exists('array_is_list')) {
+        return array_is_list($array);
+    }
+
+    if ($array === []) {
+        return true;
+    }
+
+    $current_key = 0;
+    foreach ($array as $key => $noop) {
+        if ($key !== $current_key) {
+            return false;
+        }
+        ++$current_key;
+    }
+
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
+        $fruits = array_merge(
+            ['banana', 'orange'],
+            is_array(new ArrayIterator(['durian', 'kiwi'])) ?
+                new ArrayIterator(['durian', 'kiwi']) :
+                iterator_to_array(new ArrayIterator(['durian', 'kiwi'])),
+            ['watermelon']
+        );
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

## DowngradeIsCountableRector

Downgrade `is_countable()` to former version

- class: [`Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector`](../rules/DowngradePhp73/Rector/FuncCall/DowngradeIsCountableRector.php)

```diff
 $items = [];
-return is_countable($items);
+return is_array($items) || $items instanceof Countable;
```

<br>

## DowngradeIsEnumRector

Downgrades `isEnum()` on class reflection

- class: [`Rector\DowngradePhp81\Rector\MethodCall\DowngradeIsEnumRector`](../rules/DowngradePhp81/Rector/MethodCall/DowngradeIsEnumRector.php)

```diff
 class SomeClass
 {
     public function run(ReflectionClass $reflectionClass)
     {
-        return $reflectionClass->isEnum();
+        return method_exists($reflectionClass, 'isEnum') ? $reflectionClass->isEnum() : false;
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

## DowngradeNullCoalescingOperatorRector

Remove null coalescing operator ??=

- class: [`Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector`](../rules/DowngradePhp74/Rector/Coalesce/DowngradeNullCoalescingOperatorRector.php)

```diff
 $array = [];
-$array['user_id'] ??= 'value';
+$array['user_id'] = $array['user_id'] ?? 'value';
```

<br>

## DowngradeNullsafeToTernaryOperatorRector

Change nullsafe operator to ternary operator rector

- class: [`Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector`](../rules/DowngradePhp80/Rector/NullsafeMethodCall/DowngradeNullsafeToTernaryOperatorRector.php)

```diff
-$dateAsString = $booking->getStartDate()?->asDateTimeString();
+$dateAsString = ($bookingGetStartDate = $booking->getStartDate()) ? $bookingGetStartDate->asDateTimeString() : null;
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

## DowngradeOctalNumberRector

Downgrades octal numbers to decimal ones

- class: [`Rector\DowngradePhp81\Rector\LNumber\DowngradeOctalNumberRector`](../rules/DowngradePhp81/Rector/LNumber/DowngradeOctalNumberRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        return 0o777;
+        return 0777;
     }
 }
```

<br>

## DowngradeParameterTypeWideningRector

Change param type to match the lowest type in whole family tree

:wrench: **configure it!**

- class: [`Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector`](../rules/DowngradePhp72/Rector/ClassMethod/DowngradeParameterTypeWideningRector.php)

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

## DowngradePhp72JsonConstRector

Remove Json constant that available only in php 7.2

- class: [`Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector`](../rules/DowngradePhp72/Rector/ConstFetch/DowngradePhp72JsonConstRector.php)

```diff
-$inDecoder = new Decoder($connection, true, 512, \JSON_INVALID_UTF8_IGNORE);
+$inDecoder = new Decoder($connection, true, 512, 0);

-$inDecoder = new Decoder($connection, true, 512, \JSON_INVALID_UTF8_SUBSTITUTE);
+$inDecoder = new Decoder($connection, true, 512, 0);
```

<br>

## DowngradePhp73JsonConstRector

Remove Json constant that available only in php 7.3

- class: [`Rector\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector`](../rules/DowngradePhp73/Rector/ConstFetch/DowngradePhp73JsonConstRector.php)

```diff
-json_encode($content, JSON_THROW_ON_ERROR);
+json_encode($content, 0);
+if (json_last_error() !== JSON_ERROR_NONE) {
+    throw new \Exception(json_last_error_msg());
+}
```

<br>

```diff
-$content = json_decode($json, null, 512, JSON_THROW_ON_ERROR);
+$content = json_decode($json, null, 512, 0);
+if (json_last_error() !== JSON_ERROR_NONE) {
+    throw new \Exception(json_last_error_msg());
+}
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
+
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

## DowngradeProcOpenArrayCommandArgRector

Change array command argument on proc_open to implode spaced string

- class: [`Rector\DowngradePhp74\Rector\FuncCall\DowngradeProcOpenArrayCommandArgRector`](../rules/DowngradePhp74/Rector/FuncCall/DowngradeProcOpenArrayCommandArgRector.php)

```diff
-return proc_open($command, $descriptorspec, $pipes, null, null, ['suppress_errors' => true]);
+return proc_open(is_array($command) ? implode(' ', array_map('escapeshellarg', $command)) : $command, $descriptorspec, $pipes, null, null, ['suppress_errors' => true]);
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
 function run(ReflectionClass $reflectionClass)
 {
-    return $reflectionClass->getAttributes();
+    return method_exists($reflectionClass, 'getAttributes') ? $reflectionClass->getAttributes() ? [];
 }
```

<br>

## DowngradeReflectionGetTypeRector

Downgrade reflection `$reflection->getType()` method call

- class: [`Rector\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector`](../rules/DowngradePhp74/Rector/MethodCall/DowngradeReflectionGetTypeRector.php)

```diff
 class SomeClass
 {
     public function run(ReflectionProperty $reflectionProperty)
     {
-        if ($reflectionProperty->getType()) {
+        if (method_exists($reflectionProperty, 'getType') ? $reflectionProperty->getType() ? null) {
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

## DowngradeSetAccessibleReflectionPropertyRector

Add `setAccessible()` on ReflectionProperty to allow reading private properties in PHP 8.0-

- class: [`Rector\DowngradePhp81\Rector\StmtsAwareInterface\DowngradeSetAccessibleReflectionPropertyRector`](../rules/DowngradePhp81/Rector/StmtsAwareInterface/DowngradeSetAccessibleReflectionPropertyRector.php)

```diff
 class SomeClass
 {
     public function run($object)
     {
         $reflectionProperty = new ReflectionProperty($object, 'bar');
+        $reflectionProperty->setAccessible(true);

         return $reflectionProperty->getValue($object);
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

Convert 2nd argument in `strip_tags()` from array to string

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

## DowngradeTrailingCommasInUnsetRector

Remove trailing commas in unset

- class: [`Rector\DowngradePhp73\Rector\Unset_\DowngradeTrailingCommasInUnsetRector`](../rules/DowngradePhp73/Rector/Unset_/DowngradeTrailingCommasInUnsetRector.php)

```diff
 unset(
 	$x,
-	$y,
+	$y
 );
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

## RemoveReturnTypeDeclarationFromCloneRector

Remove return type from `__clone()` method

- class: [`Rector\DowngradePhp80\Rector\ClassMethod\RemoveReturnTypeDeclarationFromCloneRector`](../rules/DowngradePhp80/Rector/ClassMethod/RemoveReturnTypeDeclarationFromCloneRector.php)

```diff
 final class SomeClass
 {
-    public function __clone(): void
+    public function __clone()
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
