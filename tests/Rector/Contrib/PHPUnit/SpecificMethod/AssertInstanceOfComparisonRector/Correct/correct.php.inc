<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInstanceOf('Foo', $something);
        $this->assertNotInstanceOf('Namespaced\Foo', $something);
    }
}