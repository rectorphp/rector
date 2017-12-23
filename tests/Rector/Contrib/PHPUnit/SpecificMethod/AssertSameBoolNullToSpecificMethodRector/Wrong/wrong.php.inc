<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(null, 'something');
        $this->assertNotSame(false, 'something', 'message');
    }
}
