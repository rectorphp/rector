<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class TryFinallyOnlyCommentStmt
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

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class TryFinallyOnlyCommentStmt
{
    public function setMultiple($values, $ttl = null): bool
    {
        try {
            return true;
        } finally {
            // only comment
        }
    }
}

?>