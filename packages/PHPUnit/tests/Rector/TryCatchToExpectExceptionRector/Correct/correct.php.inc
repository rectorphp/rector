<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    public function test()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There was an error executing the following script');
        $this->expectExceptionMessageRegExp('There was an error executing the following script');
        $someService->run();
    }
}
