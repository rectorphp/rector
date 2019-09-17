<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class IgnoreExampleClass2
{

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function unused()
    {
        return null;
    }

}

