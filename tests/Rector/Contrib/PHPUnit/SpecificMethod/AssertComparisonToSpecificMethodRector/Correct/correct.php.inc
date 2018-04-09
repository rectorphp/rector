<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertNotSame($expected, $anything);
        $this->assertGreaterThan(2, count($something));
        $this->assertLessThanOrEqual(5, count($something), 'message');
        $this->assertSame(1, count($something));
        $this->assertNotSame(true, in_array('foo', ['bar', 'baz'], true));
        $this->assertNotEquals('string', gettype($foo));
    }
}
