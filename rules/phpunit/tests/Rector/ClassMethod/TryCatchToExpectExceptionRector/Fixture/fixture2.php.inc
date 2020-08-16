<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\TryCatchToExpectExceptionRector\Fixture;

final class MyTest2 extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        try {
            $someService->run();
            $someService->moreCalls();
        } catch (Throwable $exception) {
            $this->assertSame(1000, $exception->getCode());
            $this->assertInstanceOf(RuntimeException::class, $exception);
            $this->assertEquals('There was an error executing the following script', $exception->getMessage());
        }
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\TryCatchToExpectExceptionRector\Fixture;

final class MyTest2 extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectExceptionCode(1000);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There was an error executing the following script');
        $someService->run();
        $someService->moreCalls();
    }
}

?>
