<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class KeepComment
{
    public function run()
    {
        throw new Exception();

        // keep me
    }
}
