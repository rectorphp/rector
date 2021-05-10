<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;
use Tester\TestCase;

class VariousAssertsTest extends TestCase
{
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
        // keep my comments
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

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;
use Tester\TestCase;

class VariousAssertsTest extends \PHPUnit\Framework\TestCase
{
    public function testFunctionality()
    {
        $value = 'SomeValue';
        $this->assertInstanceOf(\Kdyby\Doctrine\EntityManager::class, $value);
        $this->assertFalse(5);
        $this->assertSame('ExpectedValue', $value);
    }

    public function testExceptions()
    {
        $this->expectException('ExceptionClass');
        $this->expectExceptionMessage("Service 'one': Class or interface 'X' not found.");
        $this->expectExceptionCode(200);
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
        $this->assertFalse((bool) 'value', 'some messsage');
        // keep my comments
        $this->assertTrue(true);
    }

    public function testContains()
    {
        $value = 'some messsage';
        $this->assertStringContainsString('value', $value);
        $values = [];
        $this->assertContains('value', $values);
    }
}

?>
