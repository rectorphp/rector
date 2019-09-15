<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class IgnoreExampleClass
{

    /**
     * @noRector \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector
     */
    private function unused()
    {
        return null;
    }

}

