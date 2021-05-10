<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;
use Tester\TestCase;

class ExtensionTest extends TestCase
{
    public function setUp()
    {
    }

    public function testFunctionality()
    {
        $value = 'SomeValue';
        Assert::false(5);
    }

    public function testException()
    {
        $o = 1;
        Assert::exception(function () use ($o) {
            $o->say('');
        }, InvalidArgumentException::class, 'Invalid name');
    }
}

(new ExtensionTest())->run();

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;
use Tester\TestCase;

class ExtensionTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
    }

    public function testFunctionality()
    {
        $value = 'SomeValue';
        $this->assertFalse(5);
    }

    public function testException()
    {
        $o = 1;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid name');
        $o->say('');
    }
}

?>
