<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;

function someStaticFunctions()
{
    Assert::true(10 == 5);
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;

function someStaticFunctions()
{
    \PHPUnit\Framework\Assert::assertTrue(10 == 5);
}

?>
