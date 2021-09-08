<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use DateTime;
use stdClass;

/**
 * @param DateTime|stdClass $value
 */
function skipUnionNotTypedParam($value)
{
    if ($value instanceof stdClass) {
        return 'a';
    }

    if ($value instanceof DateTime) {
        return 'b';
    }

    return 'c';
}

?>
