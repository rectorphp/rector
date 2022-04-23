<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SkipTryFinallyAssign
{
    public function setMultiple($values, $ttl = null): bool
    {
        try {

        } finally {
            $a = 1;
        }

        return $a;
    }
}