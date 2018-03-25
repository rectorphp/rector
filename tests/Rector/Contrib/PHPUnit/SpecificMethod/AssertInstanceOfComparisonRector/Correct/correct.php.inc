<?php declare(strict_types=1);

use Foo\Bar;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInstanceOf('Foo', $something);
        $this->assertNotInstanceOf('Namespaced\Foo', $something);
        $this->assertInstanceOf('Foo\Bar\Baz', $something);
    }
}
