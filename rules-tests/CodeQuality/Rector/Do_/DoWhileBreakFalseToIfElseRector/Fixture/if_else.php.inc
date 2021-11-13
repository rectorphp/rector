<?php

namespace Rector\Tests\CodeQuality\Rector\Do_\DoWhileBreakFalseToIfElseRector\Fixture;

do {
    if (mt_rand(0, 1)) {
        $value = 5;
        break;
    } else {
        $value = 10;
    }
} while (false);

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Do_\DoWhileBreakFalseToIfElseRector\Fixture;

if (mt_rand(0, 1)) {
    $value = 5;
} else {
    $value = 10;
}

?>
