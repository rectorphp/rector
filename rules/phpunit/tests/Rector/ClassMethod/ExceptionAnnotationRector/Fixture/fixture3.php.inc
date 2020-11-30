<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

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

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

final class MyTest3 extends \PHPUnit_Framework_TestCase
{
    public function testThrowExceptionWhenOperatorIsInvalid(): void
    {
        $this->expectException(\Exception::class);
    }
}

?>
