<?php declare (strict_types=1);

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertContains('f', 'foo');
    }
}
