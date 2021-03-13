<?php

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

class SomeClass extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $nothing = 5;
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

class SomeClass extends \PHPUnit\Framework\TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function test()
    {
        $nothing = 5;
    }
}

?>
