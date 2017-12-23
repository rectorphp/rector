<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(5, count($something), 'third argument');
        $this->assertNotSame(5, count($something), 'third argument');
        $this->assertEquals(5, count($something), 'third argument');
        $this->assertNotEquals(5, count($something), 'third argument');
        $this->assertSame(5, sizeof($something), 'third argument');
        $this->assertNotSame(5, sizeof($something), 'third argument');
        $this->assertEquals(5, sizeof($something), 'third argument');
        $this->assertNotEquals(5, sizeof($something), 'third argument');
    }
}
