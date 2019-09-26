<?php

namespace Rector\Php54\Tests\Rector\Break_\RemoveZeroBreakContinueRector\Fixture;

function runContinueZeroes($random)
{
    continue 0;
    break 0;

    $five = 5;
    continue $five;

    break $random;
}

?>
-----
<?php

namespace Rector\Php54\Tests\Rector\Break_\RemoveZeroBreakContinueRector\Fixture;

function runContinueZeroes($random)
{
    continue;
    break;

    $five = 5;
    continue 5;

    break;
}

?>
