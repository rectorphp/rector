<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(5, count($something));
        $this->assertNotEquals(5, sizeof($something), 'third argument');
        $this->assertEquals('string', gettype($something));
    }
}
