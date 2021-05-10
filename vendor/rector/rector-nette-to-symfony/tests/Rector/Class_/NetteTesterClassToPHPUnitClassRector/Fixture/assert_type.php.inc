<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;
use Tester\TestCase;

class AssertTypeTest extends TestCase
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

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;
use Tester\TestCase;

class AssertTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testFunctionality()
    {
        $value = 'SomeValue';

        $this->assertIsArray($value);
        $this->assertIsArray($value);
        $this->assertIsBool($value);
        $this->assertIsCallable($value);
        $this->assertIsFloat($value);
        $this->assertIsInt($value);
        $this->assertIsInt($value);
        $this->assertNull($value);
        $this->assertIsObject($value);
        $this->assertIsResource($value);
        $this->assertIsScalar($value);
        $this->assertIsString($value);
        $this->assertInstanceOf(\stdClass::class, $value);
    }
}

?>
