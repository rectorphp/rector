<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Source;

abstract class AbstractMain
{
    public function run()
    {

    }
}

class ExtendsAbstractChild extends AbstractMain
{}