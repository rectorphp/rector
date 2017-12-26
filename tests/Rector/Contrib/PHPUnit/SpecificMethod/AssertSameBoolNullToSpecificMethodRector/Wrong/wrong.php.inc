<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(NULL, 'something');
        $this->assertNotSame(false, 'something', 'message');
    }
}
