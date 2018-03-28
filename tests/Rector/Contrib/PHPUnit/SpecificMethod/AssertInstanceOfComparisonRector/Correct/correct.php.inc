<?php declare(strict_types=1);

use Foo\Bar;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInstanceOf(Foo::class, $something);
        $this->assertNotInstanceOf(\Namespaced\Foo::class, $something);
        $this->assertInstanceOf(Bar\Baz::class, $something);
    }
}
