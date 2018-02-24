<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertFalse($something === $anything);
        $this->assertTrue(count($something) > 2);
        $this->assertFalse(count($something) >= 5, 'message');
    }
}