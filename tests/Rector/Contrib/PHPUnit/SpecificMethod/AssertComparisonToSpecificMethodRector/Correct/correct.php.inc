<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertNotSame($expected, $anything);
        $this->assertGreaterThan(2, count($something));
        $this->assertNotEquals(__DIR__, $something, 'message');
        $this->assertSame(1.0, $something);
        $this->assertNotSame(true, in_array('foo', ['bar', 'baz'], true));
        $this->assertNotEquals('string', gettype($foo));
        $this->assertEquals(['foo', 'bar'], $something);
    }
}
