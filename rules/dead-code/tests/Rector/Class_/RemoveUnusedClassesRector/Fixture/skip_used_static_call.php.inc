<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Source\SomeInterface;

class SkipUsedInStaticCall implements SomeInterface
{
    public function run()
    {
        SomeClassWithStaticCalls::wohooo();
    }
}

class SomeClassWithStaticCalls
{
    public static function wohooo()
    {
    }
}
