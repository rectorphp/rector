<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(5, count($something));
        $this->assertNotSame(5, count($something));
        $this->assertEquals(5, count($something));
        $this->assertNotEquals(5, count($something));
        $this->assertSame(5, sizeof($something));
        $this->assertNotSame(5, sizeof($something));
        $this->assertEquals(5, sizeof($something));
        $this->assertNotEquals(5, sizeof($something));
    }
}
