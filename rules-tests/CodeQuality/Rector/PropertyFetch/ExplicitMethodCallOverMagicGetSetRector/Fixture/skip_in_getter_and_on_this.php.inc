<?php

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

final class SkipInGetterAndOnThis
{
    private ?string $foo = null;

    public function run() : string
    {
        $this->foo = 'test';
        return $this->foo;
    }

    public function getFoo() : ?string
    {
        return $this->foo;
    }

    public function bar() : ?string
    {
        $this->foo = 'bar';
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }
}
?>
