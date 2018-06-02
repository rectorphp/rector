<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(1, preg_match('/^Message for ".*"\.$/', $string), $message);
        $this->assertEquals(false, preg_match('/^Message for ".*"\.$/', $string), $message);
        $this->assertNotEquals(true, preg_match('/^Message for ".*"\.$/', $string, $matches), $message);
        $this->assertNotSame(0, preg_match('/^Message for ".*"\.$/', $string), $message);
    }
}
