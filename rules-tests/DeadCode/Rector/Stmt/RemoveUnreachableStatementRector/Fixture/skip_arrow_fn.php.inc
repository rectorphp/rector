<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

// ArrowFunction_ only has 1 stmt
class SkipArrowFn
{
    public function test()
    {
        return fn(): bool => true;
    }
}