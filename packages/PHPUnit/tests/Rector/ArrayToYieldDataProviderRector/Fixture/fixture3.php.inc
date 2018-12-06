<?php

namespace Rector\PHPUnit\Tests\Rector\ArrayToYieldDataProviderRector\Wrong;

use PHPUnit\Framework\TestCase;

final class MyTestCase3 extends TestCase
{
    public function dataProvider(): array
    {
        return [[__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Correct/correct.php.inc']];
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ArrayToYieldDataProviderRector\Wrong;

use PHPUnit\Framework\TestCase;

final class MyTestCase3 extends TestCase
{
    public function dataProvider(): \Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Correct/correct.php.inc'];
    }
}

?>
