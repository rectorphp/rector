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
            /**
             * @todo Yet another important comment
             */
            ['item1'],
            /**
             * @todo One final important comment
             */
            ['item2']
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
    /**
     * @todo Let's pretend that this comment is extremely important and meaningful.
     *
     * It should not be removed by Rector.
     */
    public function provideData(): \Iterator
    {
        /**
         * @todo Yet another important comment
         */
        yield ['item1'];
        /**
         * @todo One final important comment
         */
        yield ['item2'];
    }
}

?>
