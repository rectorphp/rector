<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;

final class ShouldNotRemoveComment extends ParentTestCase
{
    public function provideData(): array
    {
        /**
         * @todo Let's pretend that this comment is extremely important and meaningful.
         *
         * It should not be removed by Rector.
         */
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

final class ShouldNotRemoveComment extends ParentTestCase
{
    public function provideData(): \Iterator
    {
        /**
         * @todo Let's pretend that this comment is extremely important and meaningful.
         *
         * It should not be removed by Rector.
         */
        yield ['item'];
    }
}

?>
