<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue($something instanceof Foo);
        $this->assertFalse($something instanceof \Namespaced\Foo);
    }
}