# All Rectors Overview

- [Projects](#projects)
- [General](#general)

## Projects

- [CakePHP\MethodCall](#cakephpmethodcall)
- [CodeQuality\Assign](#codequalityassign)
- [CodeQuality\Expression](#codequalityexpression)
- [CodeQuality\Foreach_](#codequalityforeach_)
- [CodeQuality\FuncCall](#codequalityfunccall)
- [CodeQuality\Identical](#codequalityidentical)
- [CodeQuality\Switch_](#codequalityswitch_)
- [CodeQuality\Ternary](#codequalityternary)
- [Doctrine](#doctrine)
- [DomainDrivenDesign\ValueObjectRemover](#domaindrivendesignvalueobjectremover)
- [Jms\Property](#jmsproperty)
- [PHPUnit](#phpunit)
- [PHPUnit\Foreach_](#phpunitforeach_)
- [PHPUnit\SpecificMethod](#phpunitspecificmethod)
- [PhpParser](#phpparser)
- [Php\Assign](#phpassign)
- [Php\BinaryOp](#phpbinaryop)
- [Php\ClassConst](#phpclassconst)
- [Php\ConstFetch](#phpconstfetch)
- [Php\Each](#phpeach)
- [Php\FuncCall](#phpfunccall)
- [Php\FunctionLike](#phpfunctionlike)
- [Php\List_](#phplist_)
- [Php\Name](#phpname)
- [Php\Property](#phpproperty)
- [Php\String_](#phpstring_)
- [Php\Ternary](#phpternary)
- [Php\TryCatch](#phptrycatch)
- [Php\Unset_](#phpunset_)
- [Sensio\FrameworkExtraBundle](#sensioframeworkextrabundle)
- [Silverstripe](#silverstripe)
- [Sylius\Review](#syliusreview)
- [Symfony\Console](#symfonyconsole)
- [Symfony\Controller](#symfonycontroller)
- [Symfony\DependencyInjection](#symfonydependencyinjection)
- [Symfony\Form](#symfonyform)
- [Symfony\FrameworkBundle](#symfonyframeworkbundle)
- [Symfony\HttpKernel](#symfonyhttpkernel)
- [Symfony\MethodCall](#symfonymethodcall)
- [Symfony\Process](#symfonyprocess)
- [Symfony\Validator](#symfonyvalidator)
- [Symfony\VarDumper](#symfonyvardumper)
- [Symfony\Yaml](#symfonyyaml)
- [Twig](#twig)

## CakePHP\MethodCall

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

## CodeQuality\Assign

### `CombinedAssignRector`

- class: `Rector\CodeQuality\Rector\Assign\CombinedAssignRector`

Simplify $value = $value + 5; assignments to shorter ones

```diff
-$value = $value + 5;
+$value += 5;
```

## CodeQuality\Expression

### `SimplifyMirrorAssignRector`

- class: `Rector\CodeQuality\Rector\Expression\SimplifyMirrorAssignRector`

Removes unneeded $a = $a assigns

```diff
-$a = $a;
```

## CodeQuality\Foreach_

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

## CodeQuality\FuncCall

### `InArrayAndArrayKeysToArrayKeyExistsRector`

- class: `Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector`

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
```

### `SimplifyInArrayValuesRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector`

Removes unneeded array_values() in in_array() call

```diff
-in_array("key", array_values($array), true);
+in_array("key", $array, true);
```

### `SimplifyArrayCallableRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyArrayCallableRector`

Changes redundant anonymous bool functions to simple calls

```diff
-$paths = array_filter($paths, function ($path): bool {
-    return is_dir($path);
-});
+array_filter($paths, "is_dir");
```

### `SimplifyFuncGetArgsCountRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector`

Simplify count of func_get_args() to fun_num_args()

```diff
-count(func_get_args());
+func_num_args();
```

### `SimplifyStrposLowerRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector`

Simplify strpos(strtolower(), "...") calls

```diff
-strpos(strtolower($var), "...")"
+stripos($var, "...")"
```

## CodeQuality\Identical

### `GetClassToInstanceOfRector`

- class: `Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector`

Changes comparison with get_class to instanceof

```diff
-if (EventsListener::class === get_class($event->job)) { }
+if ($event->job instanceof EventsListener) { }
```

### `SimplifyIdenticalFalseToBooleanNotRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyIdenticalFalseToBooleanNotRector`

Changes === false to negate !

```diff
-if ($something === false) {}
+if (! $something) {}
```

### `SimplifyConditionsRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector`

Simplify conditions

```diff
-if (! ($foo !== 'bar')) {...
+if ($foo === 'bar') {...
```

### `SimplifyArraySearchRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector`

Simplify array_search to in_array

```diff
-array_search("searching", $array) !== false;
+in_array("searching", $array, true);
```

## CodeQuality\Switch_

### `SimplifyBinarySwitchRector`

- class: `Rector\CodeQuality\Rector\Switch_\SimplifyBinarySwitchRector`

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

## CodeQuality\Ternary

### `UnnecessaryTernaryExpressionRector`

- class: `Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector`

Remove unnecessary ternary expressions.

```diff
-$foo === $bar ? true : false;
+$foo === $bar;
```

## Doctrine

### `AliasToClassRector`

- class: `Rector\Doctrine\Rector\AliasToClassRector`

Replaces doctrine alias with class.

```diff
 $entityManager = new Doctrine\ORM\EntityManager();
-$entityManager->getRepository("AppBundle:Post");
+$entityManager->getRepository(\App\Entity\Post::class);
```

## DomainDrivenDesign\ValueObjectRemover

### `ValueObjectRemoverDocBlockRector`

- class: `Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverDocBlockRector`

Turns defined value object to simple types in doc blocks

```yaml
services:
    Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverDocBlockRector:
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
```

```yaml
services:
    Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverDocBlockRector:
        $valueObjectsToSimpleTypes:
            ValueObject: string
```

↓

```diff
-/** @var ValueObject|null */
+/** @var string|null */
 $name;
```

### `ValueObjectRemoverRector`

- class: `Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverRector`

Remove values objects and use directly the value.

```yaml
services:
    Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverRector:
        $valueObjectsToSimpleTypes:
            ValueObject: string
```

↓

```diff
-$name = new ValueObject("name");
+$name = "name";
```

```yaml
services:
    Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverRector:
        $valueObjectsToSimpleTypes:
            ValueObject: string
```

↓

```diff
-function someFunction(ValueObject $name) { }
+function someFunction(string $name) { }
```

```yaml
services:
    Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverRector:
        $valueObjectsToSimpleTypes:
            ValueObject: string
```

↓

```diff
-function someFunction(): ValueObject { }
+function someFunction(): string { }
```

```yaml
services:
    Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverRector:
        $valueObjectsToSimpleTypes:
            ValueObject: string
```

↓

```diff
-function someFunction(): ?ValueObject { }
+function someFunction(): ?string { }
```

## Jms\Property

### `JmsInjectAnnotationRector`

- class: `Rector\Jms\Rector\Property\JmsInjectAnnotationRector`

Changes properties with @JMS\DiExtraBundle\Annotation\Inject to constructor injection

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

## PHPUnit

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

### `DelegateExceptionArgumentsRector`

- class: `Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector`

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

```diff
-$this->setExpectedException(Exception::class, "Message", "CODE");
+$this->setExpectedException(Exception::class);
+$this->expectExceptionMessage("Message");
+$this->expectExceptionCode("CODE");
```

### `ArrayToYieldDataProviderRector`

- class: `Rector\PHPUnit\Rector\ArrayToYieldDataProviderRector`

Turns method data providers in PHPUnit from arrays to yield

```diff
 /**
- * @return mixed[]
  */
-public function provide(): array
+public function provide(): Iterator
 {
-    return [
-        ['item']
-    ]
+    yield ['item'];
 }
```

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

## PHPUnit\Foreach_

### `SimplifyForeachInstanceOfRector`

- class: `Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector`

Simplify unnecessary foreach check of instances

```diff
-foreach ($foos as $foo) {
-    $this->assertInstanceOf(\SplFileInfo::class, $foo);
-}
+$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
```

## PHPUnit\SpecificMethod

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

### `AssertTrueFalseToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector`

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

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

### `IdentifierRector`

- class: `Rector\PhpParser\Rector\IdentifierRector`

Turns node string names to Identifier object in php-parser

```diff
 $constNode = new PhpParser\Node\Const_;
-$name = $constNode->name;
+$name = $constNode->name->toString();'
```

### `CatchAndClosureUseNameRector`

- class: `Rector\PhpParser\Rector\CatchAndClosureUseNameRector`

Turns `$catchNode->var` to its new `name` property in php-parser

```diff
-$catchNode->var;
+$catchNode->var->name
```

### `SetLineRector`

- class: `Rector\PhpParser\Rector\SetLineRector`

Turns standalone line method to attribute in Node of PHP-Parser

```diff
-$node->setLine(5);
+$node->setAttribute("line", 5);
```

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

## Php\Assign

### `AssignArrayToStringRector`

- class: `Rector\Php\Rector\Assign\AssignArrayToStringRector`

String cannot be turned into array by assignment anymore

```diff
-$string = '';
+$string = [];
 $string[] = 1;
```

## Php\BinaryOp

### `IsCountableRector`

- class: `Rector\Php\Rector\BinaryOp\IsCountableRector`

Changes is_array + Countable check to is_countable

```diff
-is_array($foo) || $foo instanceof Countable;
+is_countable($foo);
```

### `IsIterableRector`

- class: `Rector\Php\Rector\BinaryOp\IsIterableRector`

Changes is_array + Traversable check to is_iterable

```diff
-is_array($foo) || $foo instanceof Traversable;
+is_iterable($foo);
```

## Php\ClassConst

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

## Php\ConstFetch

### `SensitiveConstantNameRector`

- class: `Rector\Php\Rector\ConstFetch\SensitiveConstantNameRector`

Changes case insensitive constants to sensitive ones.

```diff
 define('FOO', 42, true);
 var_dump(FOO);
-var_dump(foo);
+var_dump(FOO);
```

### `BarewordStringRector`

- class: `Rector\Php\Rector\ConstFetch\BarewordStringRector`

Changes unquoted non-existing constants to strings

```diff
-var_dump(VAR);
+var("VAR");
```

## Php\Each

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

### `ListEachRector`

- class: `Rector\Php\Rector\Each\ListEachRector`

each() function is deprecated, use foreach() instead.

```diff
-list($key, $callback) = each($callbacks);
+$key = key($opt->option);
+$val = current($opt->option);
```

## Php\FuncCall

### `RandomFunctionRector`

- class: `Rector\Php\Rector\FuncCall\RandomFunctionRector`

Changes rand, srand and getrandmax by new md_* alternatives.

```diff
-rand();
+mt_rand();
```

### `TrailingCommaArgumentsRector`

- class: `Rector\Php\Rector\FuncCall\TrailingCommaArgumentsRector`

Adds trailing commas to function and methods calls

```diff
 calling(
     $one,
-    $two
+    $two,
 );
```

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

### `CallUserMethodRector`

- class: `Rector\Php\Rector\FuncCall\CallUserMethodRector`

Changes call_user_method()/call_user_method_array() to call_user_func()/call_user_func_array()

```diff
-call_user_method($method, $obj, "arg1", "arg2");
+call_user_func(array(&$obj, "method"), "arg1", "arg2");
```

### `StringifyStrNeedlesRector`

- class: `Rector\Php\Rector\FuncCall\StringifyStrNeedlesRector`

Makes needles explicit strings

```diff
 $needle = 5;
-$fivePosition = strpos('725', $needle);
+$fivePosition = strpos('725', (string) $needle);
```

### `CountOnNullRector`

- class: `Rector\Php\Rector\FuncCall\CountOnNullRector`

Changes count() on null to safe ternary check

```diff
 $values = null;
-$count = count($values);
+$count = is_array($values) || $values instanceof Countable ? count($values) : 0;
```

### `SensitiveDefineRector`

- class: `Rector\Php\Rector\FuncCall\SensitiveDefineRector`

Changes case insensitive constants to sensitive ones.

```diff
-define('FOO', 42, true);
+define('FOO', 42);
```

### `MultiDirnameRector`

- class: `Rector\Php\Rector\FuncCall\MultiDirnameRector`

Changes multiple dirname() calls to one with nesting level

```diff
-dirname(dirname($path));
+dirname($path, 2);
```

### `JsonThrowOnErrorRector`

- class: `Rector\Php\Rector\FuncCall\JsonThrowOnErrorRector`

Adds JSON_THROW_ON_ERROR to json_encode() and json_decode() to throw JsonException on error

```diff
-json_encode($content);
-json_decode($json);
+json_encode($content, JSON_THROW_ON_ERROR
+json_decode($json, null, null, JSON_THROW_ON_ERROR););
```

### `EregToPregMatchRector`

- class: `Rector\Php\Rector\FuncCall\EregToPregMatchRector`

Changes ereg*() to preg*() calls

```diff
-ereg("hi")
+preg_match("#hi#");
```

### `PowToExpRector`

- class: `Rector\Php\Rector\FuncCall\PowToExpRector`

Changes pow(val, val2) to ** (exp) parameter

```diff
-pow(1, 2);
+1**2;
```

## Php\FunctionLike

### `ExceptionHandlerTypehintRector`

- class: `Rector\Php\Rector\FunctionLike\ExceptionHandlerTypehintRector`

Changes property @var annotations from annotation to type.

```diff
-function handler(Exception $exception) { ... }
+function handler(Throwable $exception) { ... }
 set_exception_handler('handler');
```

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

## Php\List_

### `ListSplitStringRector`

- class: `Rector\Php\Rector\List_\ListSplitStringRector`

list() cannot split string directly anymore, use str_split()

```diff
-list($foo) = "string";
+list($foo) = str_split("string");
```

### `EmptyListRector`

- class: `Rector\Php\Rector\List_\EmptyListRector`

list() cannot be empty

```diff
-list() = $values;
+list($generated) = $values;
```

### `ListSwapArrayOrderRector`

- class: `Rector\Php\Rector\List_\ListSwapArrayOrderRector`

list() assigns variables in reverse order - relevant in array assign

```diff
-list($a[], $a[]) = [1, 2];
+list($a[], $a[]) = array_reverse([1, 2])];
```

## Php\Name

### `ReservedObjectRector`

- class: `Rector\Php\Rector\Name\ReservedObjectRector`

Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured

```diff
-class Object
+class SmartObject
 {
 }
```

## Php\Property

### `TypedPropertyRector`

- class: `Rector\Php\Rector\Property\TypedPropertyRector`

Changes property @var annotations from annotation to type.

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

## Php\String_

### `SensitiveHereNowDocRector`

- class: `Rector\Php\Rector\String_\SensitiveHereNowDocRector`

Changes heredoc/nowdoc that contains closing word to safe wrapper name

```diff
-    $value = <<<A
-        A
-    A
+$value = <<<A_WRAP
+    A
+A_WRAP
```

## Php\Ternary

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

## Php\TryCatch

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

## Php\Unset_

### `UnsetCastRector`

- class: `Rector\Php\Rector\Unset_\UnsetCastRector`

Removes (unset) cast

```diff
-$value = (unset) $value;
+$value = null;
```

## Sensio\FrameworkExtraBundle

### `TemplateAnnotationRector`

- class: `Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector`

Turns @Template annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony

```diff
-/**
- * @Template()
- */
 public function indexAction()
 {
+    return $this->render("index.html.twig");
 }
```

## Silverstripe

### `ConstantToStaticCallRector`

- class: `Rector\Silverstripe\Rector\ConstantToStaticCallRector`

Turns defined constant to static method call.

```diff
-SS_DATABASE_NAME;
+Environment::getEnv("SS_DATABASE_NAME");
```

### `DefineConstantToStaticCallRector`

- class: `Rector\Silverstripe\Rector\DefineConstantToStaticCallRector`

Turns defined function call to static method call.

```diff
-defined("SS_DATABASE_NAME");
+Environment::getEnv("SS_DATABASE_NAME");
```

## Sylius\Review

### `ReplaceCreateMethodWithoutReviewerRector`

- class: `Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector`

Turns `createForSubjectWithReviewer()` with null review to standalone method in Sylius

```diff
-$this->createForSubjectWithReviewer($subject, null)
+$this->createForSubject($subject)
```

## Symfony\Console

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

## Symfony\Controller

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

### `RedirectToRouteRector`

- class: `Rector\Symfony\Rector\Controller\RedirectToRouteRector`

Turns redirect to route to short helper method in Controller in Symfony

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

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

## Symfony\DependencyInjection

### `ContainerBuilderCompileEnvArgumentRector`

- class: `Rector\Symfony\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector`

Turns old default value to parameter in ContinerBuilder->build() method in DI in Symfony

```diff
-$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile();
+$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile(true);
```

## Symfony\Form

### `FormIsValidRector`

- class: `Rector\Symfony\Rector\Form\FormIsValidRector`

Adds `$form->isSubmitted()` validatoin to all `$form->isValid()` calls in Form in Symfony

```diff
-if ($form->isValid()) {
+if ($form->isSubmitted() && $form->isValid()) {
 }
```

### `OptionNameRector`

- class: `Rector\Symfony\Rector\Form\OptionNameRector`

Turns old option names to new ones in FormTypes in Form in Symfony

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

### `StringFormTypeToClassRector`

- class: `Rector\Symfony\Rector\Form\StringFormTypeToClassRector`

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony

```diff
 $formBuilder = new Symfony\Component\Form\FormBuilder;
-$formBuilder->add('name', 'form.type.text');
+$form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

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

## Symfony\FrameworkBundle

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

## Symfony\HttpKernel

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

## Symfony\MethodCall

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

## Symfony\Process

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

### `ProcessBuilderInstanceRector`

- class: `Rector\Symfony\Rector\Process\ProcessBuilderInstanceRector`

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

## Symfony\Validator

### `ConstraintUrlOptionRector`

- class: `Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector`

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

## Symfony\VarDumper

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

## Symfony\Yaml

### `SpaceBetweenKeyAndValueYamlRector`

- class: `Rector\Symfony\Rector\Yaml\SpaceBetweenKeyAndValueYamlRector`

Mappings with a colon (:) that is not followed by a whitespace will get one

```diff
-key:value
+key: value
```

### `SessionStrictTrueByDefaultYamlRector`

- class: `Rector\Symfony\Rector\Yaml\SessionStrictTrueByDefaultYamlRector`

session > use_strict_mode is true by default and can be removed

```diff
-session > use_strict_mode: true
+session:
```

### `ParseFileRector`

- class: `Rector\Symfony\Rector\Yaml\ParseFileRector`

session > use_strict_mode is true by default and can be removed

```diff
-session > use_strict_mode: true
+session:
```

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

---
## General

- [Annotation](#annotation)
- [Argument](#argument)
- [Assign](#assign)
- [Class_](#class_)
- [Constant](#constant)
- [DependencyInjection](#dependencyinjection)
- [Function_](#function_)
- [Interface_](#interface_)
- [MagicDisclosure](#magicdisclosure)
- [MethodBody](#methodbody)
- [MethodCall](#methodcall)
- [Namespace_](#namespace_)
- [Property](#property)
- [Psr4](#psr4)
- [RepositoryAsService](#repositoryasservice)
- [StaticCall](#staticcall)
- [Typehint](#typehint)
- [Visibility](#visibility)

## Annotation

### `AnnotationReplacerRector`

- class: `Rector\Rector\Annotation\AnnotationReplacerRector`

Turns defined annotations above properties and methods to their new values.

```yaml
services:
    Rector\Rector\Annotation\AnnotationReplacerRector:
        $classToAnnotationMap:
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

## Argument

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

### `ArgumentDefaultValueReplacerRector`

- class: `Rector\Rector\Argument\ArgumentDefaultValueReplacerRector`

Replaces defined map of arguments in defined methods and their calls.

```yaml
services:
    Rector\Rector\Argument\ArgumentDefaultValueReplacerRector:
        $argumentChangesByMethodAndType:
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

## Assign

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

## Class_

### `ClassReplacerRector`

- class: `Rector\Rector\Class_\ClassReplacerRector`

Replaces defined classes by new ones.

```yaml
services:
    Rector\Rector\Class_\ClassReplacerRector:
        $oldToNewClasses:
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

### `ParentClassToTraitsRector`

- class: `Rector\Rector\Class_\ParentClassToTraitsRector`

Replaces parent class to specific traits

```yaml
services:
    Rector\Rector\Class_\ParentClassToTraitsRector:
        $parentClassToTraits:
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

## Constant

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

### `ClassConstantReplacerRector`

- class: `Rector\Rector\Constant\ClassConstantReplacerRector`

Replaces defined class constants in their calls.

```yaml
services:
    Rector\Rector\Constant\ClassConstantReplacerRector:
        $oldToNewConstantsByClass:
            SomeClass:
                OLD_CONSTANT: NEW_CONSTANT
```

↓

```diff
-$value = SomeClass::OLD_CONSTANT;
+$value = SomeClass::NEW_CONSTANT;
```

## DependencyInjection

### `AnnotatedPropertyInjectToConstructorInjectionRector`

- class: `Rector\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector`

Turns non-private properties with @annotation to private properties and constructor injection

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

## Function_

### `FunctionToMethodCallRector`

- class: `Rector\Rector\Function_\FunctionToMethodCallRector`

Turns defined function calls to local method calls.

```yaml
services:
    Rector\Rector\Function_\FunctionToMethodCallRector:
        $functionToMethodCall:
            view:
                - this
                - render
```

↓

```diff
-view("...", []);
+$this->render("...", []);
```

### `FunctionToStaticCallRector`

- class: `Rector\Rector\Function_\FunctionToStaticCallRector`

Turns defined function call to static method call.

```yaml
services:
    Rector\Rector\Function_\FunctionToStaticCallRector:
        $functionToStaticCall:
            view:
                - SomeStaticClass
                - render
```

↓

```diff
-view("...", []);
+SomeClass::render("...", []);
```

### `FunctionReplaceRector`

- class: `Rector\Rector\Function_\FunctionReplaceRector`

Turns defined function call new one.

```yaml
services:
    Rector\Rector\Function_\FunctionReplaceRector:
        $functionToStaticCall:
            view: Laravel\Templating\render
```

↓

```diff
-view("...", []);
+Laravel\Templating\render("...", []);
```

## Interface_

### `MergeInterfacesRector`

- class: `Rector\Rector\Interface_\MergeInterfacesRector`

Merges old interface to a new one, that already has its methods

```yaml
services:
    Rector\Rector\Interface_\MergeInterfacesRector:
        $oldToNewInterfaces:
            SomeOldInterface: SomeInterface
```

↓

```diff
-class SomeClass implements SomeInterface, SomeOldInterface
+class SomeClass implements SomeInterface
 {
 }
```

## MagicDisclosure

### `ToStringToMethodCallRector`

- class: `Rector\Rector\MagicDisclosure\ToStringToMethodCallRector`

Turns defined code uses of "__toString()" method  to specific method calls.

```yaml
services:
    Rector\Rector\MagicDisclosure\ToStringToMethodCallRector:
        $typeToMethodCalls:
            SomeObject:
                toString: getPath
```

↓

```diff
 $someValue = new SomeObject;
-$result = (string) $someValue;
-$result = $someValue->__toString();
+$result = $someValue->someMethod();
+$result = $someValue->someMethod();
```

### `GetAndSetToMethodCallRector`

- class: `Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector`

Turns defined `__get`/`__set` to specific method calls.

```yaml
services:
    Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector:
        $typeToMethodCalls:
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

### `UnsetAndIssetToMethodCallRector`

- class: `Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector`

Turns defined `__isset`/`__unset` calls to specific method calls.

```yaml
services:
    Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector:
        $typeToMethodCalls:
            Nette\DI\Container:
                isset: hasService
```

↓

```diff
-isset($container["someKey"]);
+$container->hasService("someKey");
```

```yaml
services:
    Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector:
        -
            $typeToMethodCalls:
                Nette\DI\Container:
                    unset: removeService
```

↓

```diff
-unset($container["someKey"])
+$container->removeService("someKey");
```

## MethodBody

### `FluentReplaceRector`

- class: `Rector\Rector\MethodBody\FluentReplaceRector`

Turns fluent interface calls to classic ones.

```yaml
services:
    Rector\Rector\MethodBody\FluentReplaceRector:
        $classesToDefluent:
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

### `NormalToFluentRector`

- class: `Rector\Rector\MethodBody\NormalToFluentRector`

Turns fluent interface calls to classic ones.

```yaml
services:
    Rector\Rector\MethodBody\NormalToFluentRector:
        $fluentMethodsByType:
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

### `ReturnThisRemoveRector`

- class: `Rector\Rector\MethodBody\ReturnThisRemoveRector`

Removes "return $this;" from *fluent interfaces* for specified classes.

```yaml
services:
    Rector\Rector\MethodBody\ReturnThisRemoveRector:
        $classesToDefluent:
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

## MethodCall

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

### `MethodNameReplacerRector`

- class: `Rector\Rector\MethodCall\MethodNameReplacerRector`

Turns method names to new ones.

```yaml
services:
    Rector\Rector\MethodCall\MethodNameReplacerRector:
        $perClassOldToNewMethods:
            SomeClass:
                oldMethod: newMethod
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->oldMethod();
+$someObject->newMethod();
```

### `StaticMethodNameReplacerRector`

- class: `Rector\Rector\MethodCall\StaticMethodNameReplacerRector`

Turns method names to new ones.

```yaml
services:
    Rector\Rector\MethodCall\StaticMethodNameReplacerRector:
        $perClassOldToNewMethods:
            SomeClass:
                oldMethod:
                    - SomeClass
                    - newMethod
```

↓

```diff
-SomeClass::oldStaticMethod();
+SomeClass::newStaticMethod();
```

## Namespace_

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

### `PseudoNamespaceToNamespaceRector`

- class: `Rector\Rector\Namespace_\PseudoNamespaceToNamespaceRector`

Replaces defined Pseudo_Namespaces by Namespace\Ones.

```yaml
services:
    Rector\Rector\Namespace_\PseudoNamespaceToNamespaceRector:
        $pseudoNamespacePrefixes:
            - Some_
        $excludedClasses: {  }
```

↓

```diff
-$someService = Some_Object;
+$someService = Some\Object;
```

## Property

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

### `PropertyNameReplacerRector`

- class: `Rector\Rector\Property\PropertyNameReplacerRector`

Replaces defined old properties by new ones.

```yaml
services:
    Rector\Rector\Property\PropertyNameReplacerRector:
        $perClassOldToNewProperties:
            SomeClass:
                someOldProperty: someNewProperty
```

↓

```diff
-$someObject->someOldProperty;
+$someObject->someNewProperty;
```

## Psr4

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

## RepositoryAsService

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

## StaticCall

### `StaticCallToFunctionRector`

- class: `Rector\Rector\StaticCall\StaticCallToFunctionRector`

Turns static call to function call.

```yaml
services:
    Rector\Rector\StaticCall\StaticCallToFunctionRector:
        $staticCallToFunction:
            'OldClass::oldMethod': new_function
```

↓

```diff
-OldClass::oldMethod("args");
+new_function("args");
```

## Typehint

### `ReturnTypehintRector`

- class: `Rector\Rector\Typehint\ReturnTypehintRector`

Changes defined return typehint of method and class.

```yaml
services:
    Rector\Rector\Typehint\ReturnTypehintRector:
        $typehintForMethodByClass:
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

### `ParentTypehintedArgumentRector`

- class: `Rector\Rector\Typehint\ParentTypehintedArgumentRector`

Changes defined parent class typehints.

```yaml
services:
    Rector\Rector\Typehint\ParentTypehintedArgumentRector:
        $typehintForArgumentByMethodAndClass:
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

## Visibility

### `ChangeMethodVisibilityRector`

- class: `Rector\Rector\Visibility\ChangeMethodVisibilityRector`

Change visibility of method from parent class.

```yaml
services:
    Rector\Rector\Visibility\ChangeMethodVisibilityRector:
        $methodToVisibilityByClass:
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

### `ChangePropertyVisibilityRector`

- class: `Rector\Rector\Visibility\ChangePropertyVisibilityRector`

Change visibility of property from parent class.

```yaml
services:
    Rector\Rector\Visibility\ChangePropertyVisibilityRector:
        $propertyToVisibilityByClass:
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

### `ChangeConstantVisibilityRector`

- class: `Rector\Rector\Visibility\ChangeConstantVisibilityRector`

Change visibility of constant from parent class.

```yaml
services:
    Rector\Rector\Visibility\ChangeConstantVisibilityRector:
        Rector\Tests\Rector\Visibility\ChangeConstantVisibilityRector\Source\ParentObject:
            $constantToVisibilityByClass:
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

