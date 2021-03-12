<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;

final class MyTestCase extends ParentTestCase
{
    /**
     * @return mixed[]
     */
    public function provideData(): array
    {
        return [
            ['item']
        ];
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;

final class MyTestCase extends ParentTestCase
{
    public function provideData(): \Iterator
    {
        yield ['item'];
    }
}

?>
