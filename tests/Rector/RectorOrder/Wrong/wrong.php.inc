<?php declare (strict_types=1);

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(strpos('foo', 'f') !== false);
    }
}
