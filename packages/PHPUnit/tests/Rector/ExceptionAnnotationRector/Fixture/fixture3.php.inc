<?php

namespace Rector\PHPUnit\Tests\Rector\ExceptionAnnotationRector\Wrong;

final class MyTest3 extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testThrowExceptionWhenOperatorIsInvalid(): void
    {
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ExceptionAnnotationRector\Wrong;

final class MyTest3 extends \PHPUnit_Framework_TestCase
{
    public function testThrowExceptionWhenOperatorIsInvalid() : void
    {
        $this->expectException('Exception');
    }
}

?>
