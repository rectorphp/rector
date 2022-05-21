<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

final class SkipUsedInClosureUse
{
    public function __construct($hey, $man)
    {
        echo $hey . ' ';

        (function () use ($man) {
            echo $man;
        })();
    }
}
