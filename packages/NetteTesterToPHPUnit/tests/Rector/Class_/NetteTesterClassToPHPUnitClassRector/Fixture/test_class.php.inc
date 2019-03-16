<?php

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

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
        Assert::false(5);
    }
}

(new ExtensionTest())->run();

?>
-----
<?php

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

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
        Assert::false(5);
    }
}

?>
