<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(is_string($something));
        $this->assertFalse(is_string($something));
    }
}
