<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SkipTryFinallyOnlyCommentStmt
{
    public function setMultiple($values, $ttl = null): bool
    {
        try {
            return true;
        } finally {
            // only comment
        }

        return false;
    }
}