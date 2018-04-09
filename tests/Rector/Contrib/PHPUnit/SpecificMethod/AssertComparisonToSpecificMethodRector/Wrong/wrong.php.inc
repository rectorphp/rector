<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertFalse($expected === $anything);
        $this->assertTrue(count($something) > 2);
        $this->assertFalse(count($something) >= 5, 'message');
        $this->assertTrue(1 === count($something));
        $this->assertFalse(true === in_array('foo', ['bar', 'baz'], true));
        $this->assertTrue('string' != gettype($foo));
    }
}
