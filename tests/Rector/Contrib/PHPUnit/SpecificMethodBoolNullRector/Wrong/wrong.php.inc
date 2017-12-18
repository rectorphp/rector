<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(null, 'second argument');
        $this->assertSame(false, 'second argument');
        $this->assertSame(true, 'second argument');
    }
}
