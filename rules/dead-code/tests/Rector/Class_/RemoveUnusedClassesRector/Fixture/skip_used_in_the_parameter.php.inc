<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Source\SomeInterface;

class SkipUsedInTheParameter implements SomeInterface
{
    public function run(ClassUsedInAParameter $items)
    {
        return null;
    }
}

class ClassUsedInAParameter
{
}
