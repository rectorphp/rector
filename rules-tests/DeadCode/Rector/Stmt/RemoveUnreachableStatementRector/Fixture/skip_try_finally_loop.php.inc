<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SkipTryFinallyLoop
{
    public function setMultiple($values, $ttl = null): bool
    {
        try {

        } finally {
            while (ob_get_level() > $__outputLevel__) {
                ob_end_clean();
            }
        }

        return false;
    }
}