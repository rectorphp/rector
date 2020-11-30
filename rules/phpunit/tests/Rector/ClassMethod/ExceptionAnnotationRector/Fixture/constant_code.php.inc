<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Source\SomeConstant;

final class ConstantCode extends TestCase
{
    /**
     * @expectedExceptionCode SomeConstant::ERROR_CODE
     */
    public function testThrowExceptionWhenOperatorIsInvalid(): void
    {
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Source\SomeConstant;

final class ConstantCode extends TestCase
{
    public function testThrowExceptionWhenOperatorIsInvalid(): void
    {
        $this->expectExceptionCode(SomeConstant::ERROR_CODE);
    }
}

?>
