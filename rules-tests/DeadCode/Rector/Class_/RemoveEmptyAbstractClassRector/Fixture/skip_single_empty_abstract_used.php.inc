<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

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
