<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
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
