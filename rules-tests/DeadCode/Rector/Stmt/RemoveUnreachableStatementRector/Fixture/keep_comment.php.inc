<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class KeepComment
{
    public function run()
    {
        throw new Exception();

        // keep me
    }
}
