<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

// @see https://3v4l.org/W54bM
class TryFinally
{
    public function setMultiple($values, $ttl = null): bool
    {
        try {
            return true;
        } finally {
            echo 3;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

// @see https://3v4l.org/W54bM
class TryFinally
{
    public function setMultiple($values, $ttl = null): bool
    {
        try {
            return true;
        } finally {
            echo 3;
        }
    }
}

?>
