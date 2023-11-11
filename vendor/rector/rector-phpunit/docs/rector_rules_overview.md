# 51 Rules Overview

## AddDoesNotPerformAssertionToNonAssertingTestRector

Tests without assertion will have `@doesNotPerformAssertion`

- class: [`Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector`](../rules/PHPUnit60/Rector/ClassMethod/AddDoesNotPerformAssertionToNonAssertingTestRector.php)

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

- class: [`Rector\PHPUnit\PHPUnit100\Rector\Class_\AddProphecyTraitRector`](../rules/PHPUnit100/Rector/Class_/AddProphecyTraitRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\Class_\AddSeeTestAnnotationRector`](../rules/CodeQuality/Rector/Class_/AddSeeTestAnnotationRector.php)

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

- class: [`Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\AnnotationWithValueToAttributeRector`](../rules/AnnotationsToAttributes/Rector/Class_/AnnotationWithValueToAttributeRector.php)

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

## AssertCompareToSpecificMethodRector

Turns vague php-only method in PHPUnit TestCase to more specific

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertCompareToSpecificMethodRector`](../rules/CodeQuality/Rector/MethodCall/AssertCompareToSpecificMethodRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertComparisonToSpecificMethodRector`](../rules/CodeQuality/Rector/MethodCall/AssertComparisonToSpecificMethodRector.php)

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

## AssertEmptyNullableObjectToAssertInstanceofRector

Change `assertNotEmpty()` on an object to more clear `assertInstanceof()`

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector`](../rules/CodeQuality/Rector/MethodCall/AssertEmptyNullableObjectToAssertInstanceofRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
 {
     public function test()
     {
         $someObject = new stdClass();

-        $this->assertNotEmpty($someObject);
+        $this->assertInstanceof(stdClass::class, $someObject);
     }
 }
```

<br>

## AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector

Change `assertEquals()/assertSame()` method using float on expected argument to new specific alternatives.

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector`](../rules/CodeQuality/Rector/MethodCall/AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector.php)

```diff
-$this->assertSame(10.20, $value);
-$this->assertEquals(10.20, $value);
-$this->assertEquals(10.200, $value);
+$this->assertEqualsWithDelta(10.20, $value, PHP_FLOAT_EPSILON);
+$this->assertEqualsWithDelta(10.20, $value, PHP_FLOAT_EPSILON);
+$this->assertEqualsWithDelta(10.200, $value, PHP_FLOAT_EPSILON);
 $this->assertSame(10, $value);
```

<br>

## AssertEqualsParameterToSpecificMethodsTypeRector

Change `assertEquals()/assertNotEquals()` method parameters to new specific alternatives

- class: [`Rector\PHPUnit\PHPUnit80\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector`](../rules/PHPUnit80/Rector/MethodCall/AssertEqualsParameterToSpecificMethodsTypeRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsToSameRector`](../rules/CodeQuality/Rector/MethodCall/AssertEqualsToSameRector.php)

```diff
-$this->assertEquals(2, $result);
+$this->assertSame(2, $result);
```

<br>

## AssertFalseStrposToContainsRector

Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertFalseStrposToContainsRector`](../rules/CodeQuality/Rector/MethodCall/AssertFalseStrposToContainsRector.php)

```diff
-$this->assertFalse(strpos($anything, "foo"), "message");
+$this->assertNotContains("foo", $anything, "message");
```

<br>

## AssertInstanceOfComparisonRector

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertInstanceOfComparisonRector`](../rules/CodeQuality/Rector/MethodCall/AssertInstanceOfComparisonRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertIssetToSpecificMethodRector`](../rules/CodeQuality/Rector/MethodCall/AssertIssetToSpecificMethodRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertNotOperatorRector`](../rules/CodeQuality/Rector/MethodCall/AssertNotOperatorRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertPropertyExistsRector`](../rules/CodeQuality/Rector/MethodCall/AssertPropertyExistsRector.php)

```diff
-$this->assertFalse(property_exists(new Class, "property"));
-$this->assertTrue(property_exists(new Class, "property"));
+$this->assertClassHasAttribute("property", "Class");
+$this->assertClassNotHasAttribute("property", "Class");
```

<br>

## AssertRegExpRector

Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertRegExpRector`](../rules/CodeQuality/Rector/MethodCall/AssertRegExpRector.php)

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

## AssertSameBoolNullToSpecificMethodRector

Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector`](../rules/CodeQuality/Rector/MethodCall/AssertSameBoolNullToSpecificMethodRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector`](../rules/CodeQuality/Rector/MethodCall/AssertSameTrueFalseToAssertTrueFalseRector.php)

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

## AssertTrueFalseToSpecificMethodRector

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector`](../rules/CodeQuality/Rector/MethodCall/AssertTrueFalseToSpecificMethodRector.php)

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

<br>

## ConstructClassMethodToSetUpTestCaseRector

Change `__construct()` method in tests of `PHPUnit\Framework\TestCase` to `setUp()`, to prevent dangerous override

- class: [`Rector\PHPUnit\CodeQuality\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector`](../rules/CodeQuality/Rector/Class_/ConstructClassMethodToSetUpTestCaseRector.php)

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

- class: [`Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector`](../rules/AnnotationsToAttributes/Rector/Class_/CoversAnnotationWithValueToAttributeRector.php)

```diff
 use PHPUnit\Framework\TestCase;
+use PHPUnit\Framework\Attributes\CoversClass;
+use PHPUnit\Framework\Attributes\CoversFunction;

-/**
- * @covers SomeClass
- */
+#[CoversClass(SomeClass::class)]
+#[CoversFunction('someFunction')]
 final class SomeTest extends TestCase
 {
-    /**
-     * @covers ::someFunction()
-     */
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

## DataProviderAnnotationToAttributeRector

Change dataProvider annotations to attribute

- class: [`Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector`](../rules/AnnotationsToAttributes/Rector/ClassMethod/DataProviderAnnotationToAttributeRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
-    /**
-     * @dataProvider someMethod()
-     */
+    #[\PHPUnit\Framework\Attributes\DataProvider('test')]
     public function test(): void
     {
     }
 }
```

<br>

## DataProviderArrayItemsNewLinedRector

Change data provider in PHPUnit test case to newline per item

- class: [`Rector\PHPUnit\CodeQuality\Rector\ClassMethod\DataProviderArrayItemsNewLinedRector`](../rules/CodeQuality/Rector/ClassMethod/DataProviderArrayItemsNewLinedRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class ImageBinaryTest extends TestCase
 {
     /**
      * @dataProvider provideData()
      */
     public function testGetBytesSize(string $content, int $number): void
     {
         // ...
     }

     public static function provideData(): array
     {
-        return [['content', 8], ['content123', 11]];
+        return [
+            ['content', 8],
+            ['content123', 11]
+        ];
     }
 }
```

<br>

## DelegateExceptionArgumentsRector

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

- class: [`Rector\PHPUnit\PHPUnit60\Rector\MethodCall\DelegateExceptionArgumentsRector`](../rules/PHPUnit60/Rector/MethodCall/DelegateExceptionArgumentsRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 class SomeTest extends TestCase
 {
     public function test()
     {
-        $this->setExpectedException(SomeException::class, "Message", "CODE");
+        $this->setExpectedException(SomeException::class);
+        $this->expectExceptionMessage('Message');
+        $this->expectExceptionCode('CODE');
     }
 }
```

<br>

## DependsAnnotationWithValueToAttributeRector

Change depends annotations with value to attribute

- class: [`Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector`](../rules/AnnotationsToAttributes/Rector/ClassMethod/DependsAnnotationWithValueToAttributeRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     public function testOne() {}

-    /**
-     * @depends testOne
-     */
+    #[\PHPUnit\Framework\Attributes\Depends('testOne')]
     public function testThree(): void
     {
     }
 }
```

<br>

## ExceptionAnnotationRector

Changes ``@expectedException` annotations to `expectException*()` methods

- class: [`Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\ExceptionAnnotationRector`](../rules/PHPUnit60/Rector/ClassMethod/ExceptionAnnotationRector.php)

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

- class: [`Rector\PHPUnit\PHPUnit90\Rector\MethodCall\ExplicitPhpErrorApiRector`](../rules/PHPUnit90/Rector/MethodCall/ExplicitPhpErrorApiRector.php)

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

- class: [`Rector\PHPUnit\PHPUnit60\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector`](../rules/PHPUnit60/Rector/MethodCall/GetMockBuilderGetMockToCreateMockRector.php)

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

- class: [`Rector\PHPUnit\PHPUnit50\Rector\StaticCall\GetMockRector`](../rules/PHPUnit50/Rector/StaticCall/GetMockRector.php)

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

## PreferPHPUnitSelfCallRector

Changes PHPUnit calls from `$this->assert*()` to self::assert*()

- class: [`Rector\PHPUnit\Rector\Class_\PreferPHPUnitSelfCallRector`](../src/Rector/Class_/PreferPHPUnitSelfCallRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeClass extends TestCase
 {
     public function run()
     {
-        $this->assertEquals('expected', $result);
+        self::assertEquals('expected', $result);
     }
 }
```

<br>

## PreferPHPUnitThisCallRector

Changes PHPUnit calls from self::assert*() to `$this->assert*()`

- class: [`Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector`](../rules/CodeQuality/Rector/Class_/PreferPHPUnitThisCallRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeClass extends TestCase
 {
     public function run()
     {
-        self::assertEquals('expected', $result);
+        $this->assertEquals('expected', $result);
     }
 }
```

<br>

## PropertyExistsWithoutAssertRector

Turns PHPUnit TestCase assertObjectHasAttribute into `property_exists` comparisons

- class: [`Rector\PHPUnit\PHPUnit100\Rector\MethodCall\PropertyExistsWithoutAssertRector`](../rules/PHPUnit100/Rector/MethodCall/PropertyExistsWithoutAssertRector.php)

```diff
-$this->assertClassHasAttribute("property", "Class");
-$this->assertClassNotHasAttribute("property", "Class");
+$this->assertFalse(property_exists(new Class, "property"));
+$this->assertTrue(property_exists(new Class, "property"));
```

<br>

## RemoveDataProviderTestPrefixRector

Data provider methods cannot start with "test" prefix

- class: [`Rector\PHPUnit\PHPUnit70\Rector\Class_\RemoveDataProviderTestPrefixRector`](../rules/PHPUnit70/Rector/Class_/RemoveDataProviderTestPrefixRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\ClassMethod\RemoveEmptyTestMethodRector`](../rules/CodeQuality/Rector/ClassMethod/RemoveEmptyTestMethodRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\RemoveExpectAnyFromMockRector`](../rules/CodeQuality/Rector/MethodCall/RemoveExpectAnyFromMockRector.php)

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

- class: [`Rector\PHPUnit\PHPUnit100\Rector\MethodCall\RemoveSetMethodsMethodCallRector`](../rules/PHPUnit100/Rector/MethodCall/RemoveSetMethodsMethodCallRector.php)

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

## ReplaceTestAnnotationWithPrefixedFunctionRector

Replace `@test` with prefixed function

- class: [`Rector\PHPUnit\CodeQuality\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector`](../rules/CodeQuality/Rector/ClassMethod/ReplaceTestAnnotationWithPrefixedFunctionRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\Foreach_\SimplifyForeachInstanceOfRector`](../rules/CodeQuality/Rector/Foreach_/SimplifyForeachInstanceOfRector.php)

```diff
-foreach ($foos as $foo) {
-    $this->assertInstanceOf(SplFileInfo::class, $foo);
-}
+$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
```

<br>

## SpecificAssertContainsRector

Change `assertContains()/assertNotContains()` method to new string and iterable alternatives

- class: [`Rector\PHPUnit\PHPUnit80\Rector\MethodCall\SpecificAssertContainsRector`](../rules/PHPUnit80/Rector/MethodCall/SpecificAssertContainsRector.php)

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

- class: [`Rector\PHPUnit\PHPUnit90\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector`](../rules/PHPUnit90/Rector/MethodCall/SpecificAssertContainsWithoutIdentityRector.php)

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

- class: [`Rector\PHPUnit\PHPUnit80\Rector\MethodCall\SpecificAssertInternalTypeRector`](../rules/PHPUnit80/Rector/MethodCall/SpecificAssertInternalTypeRector.php)

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

- class: [`Rector\PHPUnit\PHPUnit100\Rector\Class_\StaticDataProviderClassMethodRector`](../rules/PHPUnit100/Rector/Class_/StaticDataProviderClassMethodRector.php)

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

- class: [`Rector\PHPUnit\PHPUnit90\Rector\Class_\TestListenerToHooksRector`](../rules/PHPUnit90/Rector/Class_/TestListenerToHooksRector.php)

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

## TestWithAnnotationToAttributeRector

Change `@testWith()` annotation to #[TestWith] attribute

- class: [`Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector`](../rules/AnnotationsToAttributes/Rector/ClassMethod/TestWithAnnotationToAttributeRector.php)

```diff
 use PHPUnit\Framework\TestCase;
+use PHPUnit\Framework\Attributes\TestWith;

 final class SomeFixture extends TestCase
 {
-    /**
-     * @testWith ["foo"]
-     *           ["bar"]
-     */
+    #[TestWith(['foo'])]
+    #[TestWith(['bar'])]
     public function test(): void
     {
     }
 }
```

<br>

## TestWithToDataProviderRector

Replace testWith annotation to data provider.

- class: [`Rector\PHPUnit\CodeQuality\Rector\Class_\TestWithToDataProviderRector`](../rules/CodeQuality/Rector/Class_/TestWithToDataProviderRector.php)

```diff
+public function dataProviderSum()
+{
+    return [
+        [0, 0, 0],
+        [0, 1, 1],
+        [1, 0, 1],
+        [1, 1, 3]
+    ];
+}
+
 /**
- * @testWith    [0, 0, 0]
- * @testWith    [0, 1, 1]
- * @testWith    [1, 0, 1]
- * @testWith    [1, 1, 3]
+ * @dataProvider dataProviderSum
  */
-public function testSum(int $a, int $b, int $expected)
+public function test(int $a, int $b, int $expected)
 {
     $this->assertSame($expected, $a + $b);
 }
```

<br>

## TicketAnnotationToAttributeRector

Change annotations with value to attribute

- class: [`Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\TicketAnnotationToAttributeRector`](../rules/AnnotationsToAttributes/Rector/Class_/TicketAnnotationToAttributeRector.php)

```diff
 use PHPUnit\Framework\TestCase;
+use PHPUnit\Framework\Attributes\Ticket;

-/**
- * @ticket 123
- */
+#[Ticket('123')]
 final class SomeTest extends TestCase
 {
 }
```

<br>

## UseSpecificWillMethodRector

Changes `$mock->will()` call to more specific method

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\UseSpecificWillMethodRector`](../rules/CodeQuality/Rector/MethodCall/UseSpecificWillMethodRector.php)

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

- class: [`Rector\PHPUnit\CodeQuality\Rector\MethodCall\UseSpecificWithMethodRector`](../rules/CodeQuality/Rector/MethodCall/UseSpecificWithMethodRector.php)

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

## WithConsecutiveRector

Refactor deprecated `withConsecutive()` to `willReturnCallback()` structure

- class: [`Rector\PHPUnit\Rector\StmtsAwareInterface\WithConsecutiveRector`](../src/Rector/StmtsAwareInterface/WithConsecutiveRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     public function run()
     {
-        $this->personServiceMock->expects($this->exactly(2))
+        $matcher = $this->exactly(2);
+
+        $this->personServiceMock->expects($matcher)
             ->method('prepare')
-            ->withConsecutive(
-                [1, 2],
-                [3, 4],
-            );
+            ->willReturnCallback(function () use ($matcher) {
+                return match ($matcher->numberOfInvocations()) {
+                    1 => [1, 2],
+                    2 => [3, 4]
+                };
+        });
     }
 }
```

<br>

## YieldDataProviderRector

Turns array return to yield in data providers

- class: [`Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector`](../rules/CodeQuality/Rector/Class_/YieldDataProviderRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest implements TestCase
 {
     public static function provideData()
     {
-        return [
-            ['some text']
-        ];
+        yield ['some text'];
     }
 }
```

<br>
