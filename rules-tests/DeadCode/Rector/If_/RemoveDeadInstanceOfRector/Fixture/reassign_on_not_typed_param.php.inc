<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use DateTime;
use stdClass;

/**
 * @param DateTime|stdClass $value
 */
function reasssignOnNotTypedParam($value)
{
    $value = rand(0, 1)
        ? new stdClass
        : new DateTime('now');

    if ($value instanceof stdClass) {
        return 'a';
    }

    if ($value instanceof DateTime) {
        return 'b';
    }

    return 'c';
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use DateTime;
use stdClass;

/**
 * @param DateTime|stdClass $value
 */
function reasssignOnNotTypedParam($value)
{
    $value = rand(0, 1)
        ? new stdClass
        : new DateTime('now');

    if ($value instanceof stdClass) {
        return 'a';
    }

    return 'b';

    return 'c';
}

?>
