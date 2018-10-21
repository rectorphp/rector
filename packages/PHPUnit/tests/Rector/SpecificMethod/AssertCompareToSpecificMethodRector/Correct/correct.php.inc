<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertCount(5, $something);
        $this->assertNotCount($count, $something, 'third argument');
        $this->assertInternalType('string', $something);
        $this->assertEquals('string', $something['property']());
        $this->assertNotCount($foo[1]->result, $this->results);
        $this->assertSame(1, $count);
        $this->assertNotSame(2, $foo->count());
        $this->assertEquals($count, $foo->sizeof);
    }
}
