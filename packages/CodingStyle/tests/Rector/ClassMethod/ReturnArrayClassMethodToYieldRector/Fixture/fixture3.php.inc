<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;

final class MyTestCase3 extends ParentTestCase
{
    public function dataProvider(): array
    {
        return [[__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Correct/correct.php.inc']];
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;

final class MyTestCase3 extends ParentTestCase
{
    public function dataProvider(): \Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Correct/correct.php.inc'];
    }
}

?>
