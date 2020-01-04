<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Source\SomeInterface;

class SkipUsedConstant implements SomeInterface
{
    public function run()
    {
        return SomeClassWithConstants::VALUE;
    }
}

class SomeClassWithConstants
{
    public const VALUE = 1;
}
