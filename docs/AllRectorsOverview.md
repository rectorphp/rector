# All 203 Rectors Overview

- [Projects](#projects)
- [General](#general)

## Projects

- [CakePHP](#cakephp)
- [CodeQuality](#codequality)
- [CodingStyle](#codingstyle)
- [DeadCode](#deadcode)
- [Doctrine](#doctrine)
- [DomainDrivenDesign](#domaindrivendesign)
- [Guzzle](#guzzle)
- [Jms](#jms)
- [NetteToSymfony](#nettetosymfony)
- [PHPStan](#phpstan)
- [PHPUnit](#phpunit)
- [Php](#php)
- [PhpParser](#phpparser)
- [Sensio](#sensio)
- [Silverstripe](#silverstripe)
- [Sylius](#sylius)
- [Symfony](#symfony)
- [Twig](#twig)

## CakePHP

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

## CodeQuality

### `CombinedAssignRector`

- class: `Rector\CodeQuality\Rector\Assign\CombinedAssignRector`

Simplify $value = $value + 5; assignments to shorter ones

```diff
-$value = $value + 5;
+$value += 5;
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

### `SimplifyTautologyTernaryRector`

- class: `Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector`

Simplify tautology ternary to value

```diff
-$value = ($fullyQualifiedTypeHint !== $typeHint) ? $fullyQualifiedTypeHint : $typeHint;
+$value = $fullyQualifiedTypeHint;
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

### `InArrayAndArrayKeysToArrayKeyExistsRector`

- class: `Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector`

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
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

### `SimplifyFuncGetArgsCountRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector`

Simplify count of func_get_args() to fun_num_args()

```diff
-count(func_get_args());
+func_num_args();
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

### `SimplifyStrposLowerRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector`

Simplify strpos(strtolower(), "...") calls

```diff
-strpos(strtolower($var), "...")"
+stripos($var, "...")"
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

### `SimplifyEmptyArrayCheckRector`

- class: `Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector`

Simplify `is_array` and `empty` functions combination into a simple identical check for an empty array

```diff
-is_array($values) && empty($values)
+$values === []
```

<br>

### `LogicalOrToBooleanOrRector`

- class: `Rector\CodeQuality\Rector\LogicalOr\LogicalOrToBooleanOrRector`

Change OR to || with more common understanding

```diff
-if ($f = false or true) {
+if (($f = false) || true) {
     return $f;
 }
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

### `SimplifyConditionsRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector`

Simplify conditions

```diff
-if (! ($foo !== 'bar')) {...
+if ($foo === 'bar') {...
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

## CodingStyle

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

### `SetTypeToCastRector`

- class: `Rector\CodingStyle\Rector\FuncCall\SetTypeToCastRector`

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

### `CompleteVarDocTypeConstantRector`

- class: `Rector\CodingStyle\Rector\ClassConst\CompleteVarDocTypeConstantRector`

Complete constant `@var` annotations for missing one, yet known.

```diff
 final class SomeClass
 {
+    /**
+     * @var int
+     */
     private const NUMBER = 5;
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

## DeadCode

### `RemoveDoubleAssignRector`

- class: `Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector`

Simplify useless double assigns

```diff
-$value = 1;
 $value = 1;
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

### `RemoveDuplicatedArrayKeyRector`

- class: `Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector`

Remove duplicated key in defined arrays.

```diff
 $item = [
     1 => 'A',
-    1 => 'A'
 ];
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

### `RemoveDeadStmtRector`

- class: `Rector\DeadCode\Rector\Stmt\RemoveDeadStmtRector`

Removes dead code statements

```diff
-$value = 5;
-$value;
+$value = 5;
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

## Jms

### `JmsInjectAnnotationRector`

- class: `Rector\Jms\Rector\Property\JmsInjectAnnotationRector`

Changes properties with `@JMS\DiExtraBundle\Annotation\Inject` to constructor injection

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

## NetteToSymfony

### `RouterListToControllerAnnotationsRector`

- class: `Rector\NetteToSymfony\Rector\RouterListToControllerAnnotationsRector`

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
-        $this->assertContains('foo', ['foo', 'bar']);
-        $this->assertNotContains('foo', ['foo', 'bar']);
+        $this->assertStringContains('foo', 'foo bar');
+        $this->assertStringNotContains('foo', 'foo bar');
+        $this->assertIterableContains('foo', ['foo', 'bar']);
+        $this->assertIterableNotContains('foo', ['foo', 'bar']);
     }
 }
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

### `AssertInstanceOfComparisonRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector`

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo instanceof Foo, "message");
+$this->assertFalse($foo instanceof Foo, "message");
```

```diff
-$this->assertInstanceOf("Foo", $foo, "message");
+$this->assertNotInstanceOf("Foo", $foo, "message");
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

## Php

### `AssignArrayToStringRector`

- class: `Rector\Php\Rector\Assign\AssignArrayToStringRector`

String cannot be turned into array by assignment anymore

```diff
-$string = '';
+$string = [];
 $string[] = 1;
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

### `UnsetCastRector`

- class: `Rector\Php\Rector\Unset_\UnsetCastRector`

Removes (unset) cast

```diff
-$value = (unset) $value;
+$value = null;
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

### `BarewordStringRector`

- class: `Rector\Php\Rector\ConstFetch\BarewordStringRector`

Changes unquoted non-existing constants to strings

```diff
-var_dump(VAR);
+var_dump("VAR");
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

### `RandomFunctionRector`

- class: `Rector\Php\Rector\FuncCall\RandomFunctionRector`

Changes rand, srand and getrandmax by new md_* alternatives.

```diff
-rand();
+mt_rand();
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

### `CallUserMethodRector`

- class: `Rector\Php\Rector\FuncCall\CallUserMethodRector`

Changes call_user_method()/call_user_method_array() to call_user_func()/call_user_func_array()

```diff
-call_user_method($method, $obj, "arg1", "arg2");
+call_user_func(array(&$obj, "method"), "arg1", "arg2");
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

### `IsObjectOnIncompleteClassRector`

- class: `Rector\Php\Rector\FuncCall\IsObjectOnIncompleteClassRector`

Incomplete class returns inverted bool on is_object()

```diff
 $incompleteObject = new __PHP_Incomplete_Class;
-$isObject = is_object($incompleteObject);
+$isObject = ! is_object($incompleteObject);
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

### `MysqlFuncCallToMysqliRector`

- class: `Rector\Php\Rector\FuncCall\MysqlFuncCallToMysqliRector`

Converts more complex mysql functions to mysqli

```diff
-mysql_drop_db($database);
+mysqli_query('DROP DATABASE ' . $database);
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

### `SensitiveDefineRector`

- class: `Rector\Php\Rector\FuncCall\SensitiveDefineRector`

Changes case insensitive constants to sensitive ones.

```diff
-define('FOO', 42, true);
+define('FOO', 42);
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

### `MultiDirnameRector`

- class: `Rector\Php\Rector\FuncCall\MultiDirnameRector`

Changes multiple dirname() calls to one with nesting level

```diff
-dirname(dirname($path));
+dirname($path, 2);
```

<br>

### `JsonThrowOnErrorRector`

- class: `Rector\Php\Rector\FuncCall\JsonThrowOnErrorRector`

Adds JSON_THROW_ON_ERROR to json_encode() and json_decode() to throw JsonException on error

```diff
-json_encode($content);
-json_decode($json);
+json_encode($content, JSON_THROW_ON_ERROR
+json_decode($json, null, null, JSON_THROW_ON_ERROR););
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

### `MbStrrposEncodingArgumentPositionRector`

- class: `Rector\Php\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector`

Change mb_strrpos() encoding argument position

```diff
-mb_strrpos($text, "abc", "UTF-8");
+mb_strrpos($text, "abc", 0, "UTF-8");
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

### `ExceptionHandlerTypehintRector`

- class: `Rector\Php\Rector\FunctionLike\ExceptionHandlerTypehintRector`

Changes property `@var` annotations from annotation to type.

```diff
-function handler(Exception $exception) { ... }
+function handler(Throwable $exception) { ... }
 set_exception_handler('handler');
```

<br>

### `ReturnTypeDeclarationRector`

- class: `Rector\Php\Rector\FunctionLike\ReturnTypeDeclarationRector`

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

### `ParamTypeDeclarationRector`

- class: `Rector\Php\Rector\FunctionLike\ParamTypeDeclarationRector`

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

### `ListEachRector`

- class: `Rector\Php\Rector\Each\ListEachRector`

each() function is deprecated, use foreach() instead.

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

### `EmptyListRector`

- class: `Rector\Php\Rector\List_\EmptyListRector`

list() cannot be empty

```diff
-list() = $values;
+list($generated) = $values;
```

<br>

### `ListSwapArrayOrderRector`

- class: `Rector\Php\Rector\List_\ListSwapArrayOrderRector`

list() assigns variables in reverse order - relevant in array assign

```diff
-list($a[], $a[]) = [1, 2];
+list($a[], $a[]) = array_reverse([1, 2])];
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

## PhpParser

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

### `IdentifierRector`

- class: `Rector\PhpParser\Rector\IdentifierRector`

Turns node string names to Identifier object in php-parser

```diff
 $constNode = new PhpParser\Node\Const_;
-$name = $constNode->name;
+$name = $constNode->name->toString();'
```

<br>

### `CatchAndClosureUseNameRector`

- class: `Rector\PhpParser\Rector\CatchAndClosureUseNameRector`

Turns `$catchNode->var` to its new `name` property in php-parser

```diff
-$catchNode->var;
+$catchNode->var->name
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

### `GetRequestRector`

- class: `Rector\Symfony\Rector\HttpKernel\GetRequestRector`

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

```diff
+use Symfony\Component\HttpFoundation\Request;
+
 class SomeController
 {
-    public function someAction()
+    public action(Request $request)
     {
-        $this->getRequest()->...();
+        $request->...();
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

### `RedirectToRouteRector`

- class: `Rector\Symfony\Rector\Controller\RedirectToRouteRector`

Turns redirect to route to short helper method in Controller in Symfony

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

<br>

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

### `ConstraintUrlOptionRector`

- class: `Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector`

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
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

### `StringToArrayArgumentProcessRector`

- class: `Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector`

Changes Process string argument to an array

```diff
 use Symfony\Component\Process\Process;
-$process = new Process('ls -l');
+$process = new Process(['ls', '-l']);
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

### `ParseFileRector`

- class: `Rector\Symfony\Rector\Yaml\ParseFileRector`

session > use_strict_mode is true by default and can be removed

```diff
-session > use_strict_mode: true
+session:
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

### `OptionNameRector`

- class: `Rector\Symfony\Rector\Form\OptionNameRector`

Turns old option names to new ones in FormTypes in Form in Symfony

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
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

-    public function getFilters()
+    public function getFilteres()
     {
         return [
-            'is_mobile' => new Twig_Filter_Method($this, 'isMobile'),
+             new Twig_SimpleFilter('is_mobile', [$this, 'isMobile']),
         ];
     }
 }
```

<br>

---
## General

- [Core](#core)

## Core

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

### `MethodNameReplacerRector`

- class: `Rector\Rector\MethodCall\MethodNameReplacerRector`

Turns method names to new ones.

```yaml
services:
    Rector\Rector\MethodCall\MethodNameReplacerRector:
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

### `StaticMethodNameReplacerRector`

- class: `Rector\Rector\MethodCall\StaticMethodNameReplacerRector`

Turns method names to new ones.

```yaml
services:
    Rector\Rector\MethodCall\StaticMethodNameReplacerRector:
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
    Rector\Rector\MethodCall\StaticMethodNameReplacerRector:
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

### `ArgumentAdderRector`

- class: `Rector\Rector\Argument\ArgumentAdderRector`

This Rector adds new default arguments in calls of defined methods and class types.

```yaml
services:
    Rector\Rector\Argument\ArgumentAdderRector:
        SomeExampleClass:
            someMethod:
                -
                    default_value: 'true'
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
                    default_value: 'true'
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

### `NamespaceReplacerRector`

- class: `Rector\Rector\Namespace_\NamespaceReplacerRector`

Replaces old namespace by new one.

```yaml
services:
    Rector\Rector\Namespace_\NamespaceReplacerRector:
        $oldToNewNamespaces:
            SomeOldNamespace: SomeNewNamespace
```

↓

```diff
-$someObject = new SomeOldNamespace\SomeClass;
+$someObject = new SomeNewNamespace\SomeClass;
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
-$someService = new Some_Object;
+$someService = new Some\Object;
 $someClassToKeep = new Some_Class_To_Keep;
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

### `FunctionReplaceRector`

- class: `Rector\Rector\Function_\FunctionReplaceRector`

Turns defined function call new one.

```yaml
services:
    Rector\Rector\Function_\FunctionReplaceRector:
        view: Laravel\Templating\render
```

↓

```diff
-view("...", []);
+Laravel\Templating\render("...", []);
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

### `PropertyNameReplacerRector`

- class: `Rector\Rector\Property\PropertyNameReplacerRector`

Replaces defined old properties by new ones.

```yaml
services:
    Rector\Rector\Property\PropertyNameReplacerRector:
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

### `AnnotationReplacerRector`

- class: `Rector\Rector\Annotation\AnnotationReplacerRector`

Turns defined annotations above properties and methods to their new values.

```yaml
services:
    Rector\Rector\Annotation\AnnotationReplacerRector:
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

### `ClassConstantReplacerRector`

- class: `Rector\Rector\Constant\ClassConstantReplacerRector`

Replaces defined class constants in their calls.

```yaml
services:
    Rector\Rector\Constant\ClassConstantReplacerRector:
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

### `ClassReplacerRector`

- class: `Rector\Rector\Class_\ClassReplacerRector`

Replaces defined classes by new ones.

```yaml
services:
    Rector\Rector\Class_\ClassReplacerRector:
        SomeOldClass: SomeNewClass
```

↓

```diff
-use SomeOldClass;
+use SomeNewClass;

-function (SomeOldClass $someOldClass): SomeOldClass
+function (SomeNewClass $someOldClass): SomeNewClass
 {
-    if ($someOldClass instanceof SomeOldClass) {
-        return new SomeOldClass;
+    if ($someOldClass instanceof SomeNewClass) {
+        return new SomeNewClass;
     }
 }
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

