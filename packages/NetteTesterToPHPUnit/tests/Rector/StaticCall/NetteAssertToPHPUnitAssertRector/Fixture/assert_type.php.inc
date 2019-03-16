<?php

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\StaticCall\NetteAssertToPHPUnitAssertRector\Fixture;

use Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Source\NetteTesterTestCase;
use Tester\Assert;

class AssertTypeTest extends NetteTesterTestCase
{
    public function testFunctionality()
    {
        $value = 'SomeValue';

        Assert::type('list', $value);
        Assert::type('array', $value);
        Assert::type('bool', $value);
        Assert::type('callable', $value);
        Assert::type('float', $value);
        Assert::type('int', $value);
        Assert::type('integer', $value);
        Assert::type('null', $value);
        Assert::type('object', $value);
        Assert::type('resource', $value);
        Assert::type('scalar', $value);
        Assert::type('string', $value);
        Assert::type(\stdClass::class, $value);
    }
}

?>
-----
<?php

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\StaticCall\NetteAssertToPHPUnitAssertRector\Fixture;

use Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Source\NetteTesterTestCase;
use Tester\Assert;

class AssertTypeTest extends NetteTesterTestCase
{
    public function testFunctionality()
    {
        $value = 'SomeValue';

        self::assertIsArray($value);
        self::assertIsArray($value);
        self::assertIsBool($value);
        self::assertIsCallable($value);
        self::assertIsFloat($value);
        self::assertIsInt($value);
        self::assertIsInt($value);
        self::assertNull($value);
        self::assertIsObject($value);
        self::assertIsResource($value);
        self::assertIsScalar($value);
        self::assertIsString($value);
        self::assertInstanceOf(\stdClass::class, $value);
    }
}

?>
