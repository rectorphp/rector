# 45 Rules Overview

## AddDoesNotPerformAssertionToNonAssertingTestRector

Tests without assertion will have `@doesNotPerformAssertion`

- class: [`Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector`](../src/Rector/ClassMethod/AddDoesNotPerformAssertionToNonAssertingTestRector.php)

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

## AddProphecyTraitRector

Add Prophecy trait for method using `$this->prophesize()`

- class: [`Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector`](../src/Rector/Class_/AddProphecyTraitRector.php)

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

## AddSeeTestAnnotationRector

Add `@see` annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.

- class: [`Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector`](../src/Rector/Class_/AddSeeTestAnnotationRector.php)

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

## AnnotationWithValueToAttributeRector

Change annotations with value to attribute

:wrench: **configure it!**

- class: [`Rector\PHPUnit\Rector\Class_\AnnotationWithValueToAttributeRector`](../src/Rector/Class_/AnnotationWithValueToAttributeRector.php)

```php
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\AnnotationWithValueToAttributeRector;
use Rector\PHPUnit\ValueObject\AnnotationWithValueToAttribute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(
        AnnotationWithValueToAttributeRector::class,
        [new AnnotationWithValueToAttribute('backupGlobals', 'PHPUnit\Framework\Attributes\BackupGlobals', [
            true,
            false,
        ])]
    );
};
```

↓

```diff
 use PHPUnit\Framework\TestCase;
+use PHPUnit\Framework\Attributes\BackupGlobals;

-/**
- * @backupGlobals enabled
- */
+#[BackupGlobals(true)]
 final class SomeTest extends TestCase
 {
 }
```

<br>

## ArrayArgumentToDataProviderRector

Move array argument from tests into data provider [configurable]

:wrench: **configure it!**

- class: [`Rector\PHPUnit\Rector\Class_\ArrayArgumentToDataProviderRector`](../src/Rector/Class_/ArrayArgumentToDataProviderRector.php)

```php
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\ArrayArgumentToDataProviderRector;
use Rector\PHPUnit\ValueObject\ArrayArgumentToDataProvider;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ArrayArgumentToDataProviderRector::class, [
        ArrayArgumentToDataProviderRector::ARRAY_ARGUMENTS_TO_DATA_PROVIDERS => [
            new ArrayArgumentToDataProvider('PHPUnit\Framework\TestCase', 'doTestMultiple', 'doTestSingle', 'number'),
        ],
    ]);
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

## AssertCompareToSpecificMethodRector

Turns vague php-only method in PHPUnit TestCase to more specific

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector`](../src/Rector/MethodCall/AssertCompareToSpecificMethodRector.php)

```diff
-$this->assertSame(10, count($anything), "message");
+$this->assertCount(10, $anything, "message");
```

<br>

```diff
-$this->assertNotEquals(get_class($value), SomeInstance::class);
+$this->assertNotInstanceOf(SomeInstance::class, $value);
```

<br>

## AssertComparisonToSpecificMethodRector

Turns comparison operations to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector`](../src/Rector/MethodCall/AssertComparisonToSpecificMethodRector.php)

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

## AssertEqualsParameterToSpecificMethodsTypeRector

Change `assertEquals()/assertNotEquals()` method parameters to new specific alternatives

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector`](../src/Rector/MethodCall/AssertEqualsParameterToSpecificMethodsTypeRector.php)

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

## AssertEqualsToSameRector

Turns `assertEquals()` into stricter `assertSame()` for scalar values in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector`](../src/Rector/MethodCall/AssertEqualsToSameRector.php)

```diff
-$this->assertEquals(2, $result);
+$this->assertSame(2, $result);
```

<br>

## AssertFalseStrposToContainsRector

Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector`](../src/Rector/MethodCall/AssertFalseStrposToContainsRector.php)

```diff
-$this->assertFalse(strpos($anything, "foo"), "message");
+$this->assertNotContains("foo", $anything, "message");
```

<br>

## AssertInstanceOfComparisonRector

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector`](../src/Rector/MethodCall/AssertInstanceOfComparisonRector.php)

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

## AssertIssetToSpecificMethodRector

Turns isset comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertIssetToSpecificMethodRector`](../src/Rector/MethodCall/AssertIssetToSpecificMethodRector.php)

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

## AssertNotOperatorRector

Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertNotOperatorRector`](../src/Rector/MethodCall/AssertNotOperatorRector.php)

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

## AssertPropertyExistsRector

Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector`](../src/Rector/MethodCall/AssertPropertyExistsRector.php)

```diff
-$this->assertFalse(property_exists(new Class, "property"));
-$this->assertTrue(property_exists(new Class, "property"));
+$this->assertClassHasAttribute("property", "Class");
+$this->assertClassNotHasAttribute("property", "Class");
```

<br>

## AssertRegExpRector

Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertRegExpRector`](../src/Rector/MethodCall/AssertRegExpRector.php)

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

## AssertResourceToClosedResourceRector

Turns `assertIsNotResource()` into stricter `assertIsClosedResource()` for resource values in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertResourceToClosedResourceRector`](../src/Rector/MethodCall/AssertResourceToClosedResourceRector.php)

```diff
-$this->assertIsNotResource($aResource, "message");
+$this->assertIsClosedResource($aResource, "message");
```

<br>

## AssertSameBoolNullToSpecificMethodRector

Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector`](../src/Rector/MethodCall/AssertSameBoolNullToSpecificMethodRector.php)

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

## AssertSameTrueFalseToAssertTrueFalseRector

Change `$this->assertSame(true,` ...) to `assertTrue()`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector`](../src/Rector/MethodCall/AssertSameTrueFalseToAssertTrueFalseRector.php)

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

## AssertTrueFalseInternalTypeToSpecificMethodRector

Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector`](../src/Rector/MethodCall/AssertTrueFalseInternalTypeToSpecificMethodRector.php)

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

## AssertTrueFalseToSpecificMethodRector

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector`](../src/Rector/MethodCall/AssertTrueFalseToSpecificMethodRector.php)

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

<br>

## ConstructClassMethodToSetUpTestCaseRector

Change `__construct()` method in tests of `PHPUnit\Framework\TestCase` to `setUp()`, to prevent dangerous override

- class: [`Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector`](../src/Rector/Class_/ConstructClassMethodToSetUpTestCaseRector.php)

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

## CoversAnnotationWithValueToAttributeRector

Change covers annotations with value to attribute

- class: [`Rector\PHPUnit\Rector\Class_\CoversAnnotationWithValueToAttributeRector`](../src/Rector/Class_/CoversAnnotationWithValueToAttributeRector.php)

```diff
 use PHPUnit\Framework\TestCase;
+use PHPUnit\Framework\Attributes\CoversClass;
+use PHPUnit\Framework\Attributes\CoversFunction;

-/**
- * @covers SomeClass
- */
+#[CoversClass(SomeClass::class)]
 final class SomeTest extends TestCase
 {
-    /**
-     * @covers ::someFunction
-     */
+    #[CoversFunction('someFunction')]
     public function test()
     {
     }
 }
```

<br>

## CreateMockToAnonymousClassRector

Change `$this->createMock()` with methods to direct anonymous class

- class: [`Rector\PHPUnit\Rector\ClassMethod\CreateMockToAnonymousClassRector`](../src/Rector/ClassMethod/CreateMockToAnonymousClassRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     public function test()
     {
-        $someMockObject = $this->createMock(SomeClass::class);
-
-        $someMockObject->method('someMethod')
-            ->willReturn(100);
+        $someMockObject = new class extends SomeClass {
+            public function someMethod()
+            {
+                return 100;
+            }
+        };
     }
 }
```

<br>

## CreateMockToCreateStubRector

Replaces `createMock()` with `createStub()` when relevant

- class: [`Rector\PHPUnit\Rector\MethodCall\CreateMockToCreateStubRector`](../src/Rector/MethodCall/CreateMockToCreateStubRector.php)

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

## DelegateExceptionArgumentsRector

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

- class: [`Rector\PHPUnit\Rector\MethodCall\DelegateExceptionArgumentsRector`](../src/Rector/MethodCall/DelegateExceptionArgumentsRector.php)

```diff
-$this->setExpectedException(SomeException::class, "Message", "CODE");
+$this->setExpectedException(SomeException::class);
+$this->expectExceptionMessage('Message');
+$this->expectExceptionCode('CODE');
```

<br>

## ExceptionAnnotationRector

Changes ``@expectedException` annotations to `expectException*()` methods

- class: [`Rector\PHPUnit\Rector\ClassMethod\ExceptionAnnotationRector`](../src/Rector/ClassMethod/ExceptionAnnotationRector.php)

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

## ExplicitPhpErrorApiRector

Use explicit API for expecting PHP errors, warnings, and notices

- class: [`Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector`](../src/Rector/MethodCall/ExplicitPhpErrorApiRector.php)

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

## GetMockBuilderGetMockToCreateMockRector

Remove `getMockBuilder()` to `createMock()`

- class: [`Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector`](../src/Rector/MethodCall/GetMockBuilderGetMockToCreateMockRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
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

## GetMockRector

Turns getMock*() methods to `createMock()`

- class: [`Rector\PHPUnit\Rector\StaticCall\GetMockRector`](../src/Rector/StaticCall/GetMockRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     public function test()
     {
-        $classMock = $this->getMock("Class");
+        $classMock = $this->createMock("Class");
     }
 }
```

<br>

## ProphecyPHPDocRector

Add correct `@var` to ObjectProphecy instances based on `$this->prophesize()` call.

- class: [`Rector\PHPUnit\Rector\Class_\ProphecyPHPDocRector`](../src/Rector/Class_/ProphecyPHPDocRector.php)

```diff
 class HelloTest extends TestCase
 {
     /**
-     * @var SomeClass
+     * @var ObjectProphecy<SomeClass>
      */
     private $propesizedObject;

     public function setUp(): void
     {
         $this->propesizedObject = $this->prophesize(SomeClass::class);
     }
 }
```

<br>

## RemoveDataProviderTestPrefixRector

Data provider methods cannot start with "test" prefix

- class: [`Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector`](../src/Rector/Class_/RemoveDataProviderTestPrefixRector.php)

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

## RemoveEmptyTestMethodRector

Remove empty test methods

- class: [`Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector`](../src/Rector/ClassMethod/RemoveEmptyTestMethodRector.php)

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

## RemoveExpectAnyFromMockRector

Remove `expect($this->any())` from mocks as it has no added value

- class: [`Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector`](../src/Rector/MethodCall/RemoveExpectAnyFromMockRector.php)

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

## RemoveSetMethodsMethodCallRector

Remove `"setMethods()"` method as never used

- class: [`Rector\PHPUnit\Rector\MethodCall\RemoveSetMethodsMethodCallRector`](../src/Rector/MethodCall/RemoveSetMethodsMethodCallRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     public function test()
     {
         $someMock = $this->getMockBuilder(SomeClass::class)
-            ->setMethods(['run'])
             ->getMock();
     }
 }
```

<br>

## ReplaceAssertArraySubsetWithDmsPolyfillRector

Change `assertArraySubset()` to static call of DMS\PHPUnitExtensions\ArraySubset\Assert

- class: [`Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector`](../src/Rector/MethodCall/ReplaceAssertArraySubsetWithDmsPolyfillRector.php)

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

## ReplaceTestAnnotationWithPrefixedFunctionRector

Replace `@test` with prefixed function

- class: [`Rector\PHPUnit\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector`](../src/Rector/ClassMethod/ReplaceTestAnnotationWithPrefixedFunctionRector.php)

```diff
 class SomeTest extends \PHPUnit\Framework\TestCase
 {
-    /**
-     * @test
-     */
-    public function onePlusOneShouldBeTwo()
+    public function testOnePlusOneShouldBeTwo()
     {
         $this->assertSame(2, 1+1);
     }
 }
```

<br>

## SimplifyForeachInstanceOfRector

Simplify unnecessary foreach check of instances

- class: [`Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector`](../src/Rector/Foreach_/SimplifyForeachInstanceOfRector.php)

```diff
-foreach ($foos as $foo) {
-    $this->assertInstanceOf(SplFileInfo::class, $foo);
-}
+$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
```

<br>

## SpecificAssertContainsRector

Change `assertContains()/assertNotContains()` method to new string and iterable alternatives

- class: [`Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector`](../src/Rector/MethodCall/SpecificAssertContainsRector.php)

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

## SpecificAssertContainsWithoutIdentityRector

Change `assertContains()/assertNotContains()` with non-strict comparison to new specific alternatives

- class: [`Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector`](../src/Rector/MethodCall/SpecificAssertContainsWithoutIdentityRector.php)

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

## SpecificAssertInternalTypeRector

Change `assertInternalType()/assertNotInternalType()` method to new specific alternatives

- class: [`Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector`](../src/Rector/MethodCall/SpecificAssertInternalTypeRector.php)

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

## StaticDataProviderClassMethodRector

Change data provider methods to static

- class: [`Rector\PHPUnit\Rector\Class_\StaticDataProviderClassMethodRector`](../src/Rector/Class_/StaticDataProviderClassMethodRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     /**
      * @dataProvider provideData()
      */
     public function test()
     {
     }

-    public function provideData()
+    public static function provideData()
     {
         yield [1];
     }
 }
```

<br>

## TestListenerToHooksRector

Refactor "*TestListener.php" to particular "*Hook.php" files

- class: [`Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector`](../src/Rector/Class_/TestListenerToHooksRector.php)

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

## TryCatchToExpectExceptionRector

Turns try/catch to `expectException()` call

- class: [`Rector\PHPUnit\Rector\ClassMethod\TryCatchToExpectExceptionRector`](../src/Rector/ClassMethod/TryCatchToExpectExceptionRector.php)

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

## UseSpecificWillMethodRector

Changes `$mock->will()` call to more specific method

- class: [`Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector`](../src/Rector/MethodCall/UseSpecificWillMethodRector.php)

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $translator = $this->createMock('SomeClass');
         $translator->expects($this->any())
             ->method('trans')
-            ->will($this->returnValue('translated max {{ max }}!'));
+            ->willReturnValue('translated max {{ max }}!');
     }
 }
```

<br>

## UseSpecificWithMethodRector

Changes `->with()` to more specific method

- class: [`Rector\PHPUnit\Rector\MethodCall\UseSpecificWithMethodRector`](../src/Rector/MethodCall/UseSpecificWithMethodRector.php)

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $translator = $this->createMock('SomeClass');

         $translator->expects($this->any())
             ->method('trans')
-            ->with($this->equalTo('old max {{ max }}!'));
+            ->with('old max {{ max }}!');
     }
 }
```

<br>
