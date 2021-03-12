<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\TryCatchToExpectExceptionRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    public function test()
    {
        try {
            $someService->run();
        } catch (Throwable $exception) {
            $this->assertInstanceOf(RuntimeException::class, $exception);
            $this->assertSame('There was an error executing the following script', $exception->getMessage());
            $this->assertContains('There was an error executing the following script', $exception->getMessage());
        }
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\TryCatchToExpectExceptionRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    public function test()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There was an error executing the following script');
        $this->expectExceptionMessageRegExp('#There was an error executing the following script#');
        $someService->run();
    }
}

?>
