<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertFalse($expected === $anything);
        $this->assertTrue(count($something) > 2);
        $this->assertTrue(__DIR__ <> $something, 'message');
        $this->assertTrue(1.0 === $something);
        $this->assertFalse(true === in_array('foo', ['bar', 'baz'], true));
        $this->assertTrue('string' != gettype($foo));
        $this->assertTrue(['foo', 'bar'] == $something);
    }
}
