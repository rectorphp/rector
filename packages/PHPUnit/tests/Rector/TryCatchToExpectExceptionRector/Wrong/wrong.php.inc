<?php declare(strict_types=1);

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
