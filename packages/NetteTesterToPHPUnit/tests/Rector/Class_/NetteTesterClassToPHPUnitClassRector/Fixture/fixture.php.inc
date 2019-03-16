<?php

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

namespace KdybyTests\Doctrine;

use Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Source\NetteTesterTestCase;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

class ExtensionTest extends NetteTesterTestCase
{
    public function setUp()
    {
    }

    public function testFunctionality()
    {
        $value = 'SomeValue';
        Assert::type(\Kdyby\Doctrine\EntityManager::class, $value);
        Assert::false(5);
        Assert::same('ExpectedValue', $value);
    }

    public function testExceptions()
    {
        Assert::exception(function () {
            $builder = new DI\ContainerBuilder;
            $builder->run();
        }, 'ExceptionClass', "Service 'one': Class or interface 'X' not found.", 200);
    }

    public function testNoError()
    {
        Assert::noError(function () {
            $value = 1;
        });
    }

    public function testY()
    {
        Assert::falsey('value', 'some messsage');
        Assert::truthy(true);
    }

    public function testContains()
    {
        $value = 'some messsage';
        Assert::contains('value', $value);
        $values = [];
        Assert::contains('value', $values);
    }
}

(new ExtensionTest())->run();

?>
-----
<?php

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

namespace KdybyTests\Doctrine;

use Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Source\NetteTesterTestCase;
use Tester\Assert;

class ExtensionTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
    }

    public function testFunctionality()
    {
        $value = 'SomeValue';
        self::assertInstanceOf(\Kdyby\Doctrine\EntityManager::class, $value);
        self::assertFalse(5);
        self::assertSame('ExpectedValue', $value);
    }

    public function testExceptions()
    {
        self::expectException('ExceptionClass');
        self::expectExceptionMessage("Service 'one': Class or interface 'X' not found.");
        self::expectExceptionCode(200);
        $builder = new DI\ContainerBuilder;
        $builder->run();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testNoError()
    {
        $value = 1;
    }

    public function testY()
    {
        self::assertFalse((bool) 'value', 'some messsage');
        self::assertTrue(true);
    }

    public function testContains()
    {
        $value = 'some messsage';
        self::assertStringContainsString('value', $value);
        $values = [];
        self::assertContains('value', $values);
    }
}

?>
