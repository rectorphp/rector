<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There was an error executing the following script');
        $someService->run();
        $someService->moreCalls();
    }
}
