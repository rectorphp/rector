<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Fixture;

class SomeProperty
{
    public $value;
}

trait InteractsWithProperty
{
    public function doSomething(SomeProperty $property)
    {
        $property->value->doSomething();
    }
}

final class SkipMixedScope
{
    use InteractsWithProperty;
}
