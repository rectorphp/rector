<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertNotFalse(strpos($node, 'foo'));
        $this->assertFalse(strpos($node, 'foo'), 'message');
    }
}
