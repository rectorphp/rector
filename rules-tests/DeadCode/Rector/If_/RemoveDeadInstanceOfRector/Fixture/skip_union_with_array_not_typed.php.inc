<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use DateTime;
use stdClass;

/**
 * @var array|stdClass[] $lists
 */
$lists = rand(0, 1)
    ? [new stdClass()]
    : [new DateTime('now')];

foreach ($lists as $key => $list) {
    if (! $list instanceof stdClass) {
        $lists[$key] = new stdClass;
    }
}

?>
