<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector\Fixture;

function ternaryToNullCoalescing3()
{
    $f = $g === null ? 'g' : $g;

    $ff = null === $gg ? 'gg' : $gg;
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector\Fixture;

function ternaryToNullCoalescing3()
{
    $f = $g ?? 'g';

    $ff = $gg ?? 'gg';
}

?>
