<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

final class MyTest2 extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Phpml\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid operator "~=" provided
     */
    public function testThrowExceptionWhenOperatorIsInvalid(): void
    {
        Comparison::compare(1, 1, '~=');

        // extra line
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

final class MyTest2 extends \PHPUnit_Framework_TestCase
{
    public function testThrowExceptionWhenOperatorIsInvalid(): void
    {
        $this->expectException('Phpml\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid operator "~=" provided');
        Comparison::compare(1, 1, '~=');

        // extra line
    }
}

?>
