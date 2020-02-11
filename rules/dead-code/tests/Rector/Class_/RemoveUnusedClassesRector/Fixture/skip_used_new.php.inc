<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Source\SomeInterface;

class SkipUsedNew implements SomeInterface
{
    public function run()
    {
        $values = new SomeClassToBeCreated();
    }
}

class SomeClassToBeCreated
{

}
