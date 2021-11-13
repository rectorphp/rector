<?php

namespace Rector\Tests\CodeQuality\Rector\Do_\DoWhileBreakFalseToIfElseRector\Fixture;

do {
    if (mt_rand(0, 1)) {
        $value = 5;
        break;
    }

    if (mt_rand(0, 2)) {
        $value = 10;
        break;
    }

    $value = 25;
} while (false);

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Do_\DoWhileBreakFalseToIfElseRector\Fixture;

if (mt_rand(0, 1)) {
    $value = 5;
} else {
    if (mt_rand(0, 2)) {
        $value = 10;
    } else {
        $value = 25;
    }
}

?>
