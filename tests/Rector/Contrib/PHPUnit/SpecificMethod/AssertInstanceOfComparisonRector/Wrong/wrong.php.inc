<?php declare(strict_types=1);

use Foo\Bar;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue($something instanceof Foo);
        $this->assertFalse($something instanceof \Namespaced\Foo);
        $this->assertTrue($something instanceof Bar\Baz);
    }
}
