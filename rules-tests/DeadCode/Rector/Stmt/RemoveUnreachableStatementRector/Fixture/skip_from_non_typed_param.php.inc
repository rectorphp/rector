<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use InvalidArgumentException;
use stdClass;

/**
 * @param stdClass|array $item
 */
function SkipFromNonTypedParam($item)
{
    if ($item instanceof stdClass) {
        return 1;
    }

    if (is_array($item)) {
        return 2;
    }

    throw new InvalidArgumentException();
}

?>
