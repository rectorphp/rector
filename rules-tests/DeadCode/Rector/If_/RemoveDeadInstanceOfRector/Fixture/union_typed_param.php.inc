<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use DateTime;
use stdClass;

function unionTypedParam(DateTime|stdClass $value)
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
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use DateTime;
use stdClass;

function unionTypedParam(DateTime|stdClass $value)
{
    if ($value instanceof stdClass) {
        return 'a';
    }

    return 'b';

    return 'c';
}

?>
