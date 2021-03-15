<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class SkipSingleEmptyAbstractUsed
{
}

class Single2
{
    function run($some)
    {
        if ($some instanceof SkipSingleEmptyAbstractUsed) {

        }
    }
}

?>
