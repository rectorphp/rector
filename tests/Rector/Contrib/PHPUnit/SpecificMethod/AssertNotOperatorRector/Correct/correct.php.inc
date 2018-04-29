<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue($something === false);
        $this->assertFalse($something);
        self::assertFalse($something);
    }
}
