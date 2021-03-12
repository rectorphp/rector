<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector\Fixture;

function ternaryToNullCoalescing()
{
    $x = isset($a) ? $a : null;

    $x = isset($a[0]) ? $a[0] : 1;

    $x = isset($a) ? $a : null;

    $x = isset   ($a[$b["c"]]) ? $a[$b["c"]] : null;

    $x = isset($a) ? $a : $b[func(1, true)];

    $x = (isset($a) ? $a : isset($b)) ? $b : "";

    $x = ((isset($a) ? $a : isset($b)) ? $b : isset($c)) ? $c : "";
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector\Fixture;

function ternaryToNullCoalescing()
{
    $x = $a ?? null;

    $x = $a[0] ?? 1;

    $x = $a ?? null;

    $x = $a[$b["c"]] ?? null;

    $x = $a ?? $b[func(1, true)];

    $x = ($a ?? isset($b)) ? $b : "";

    $x = (($a ?? isset($b)) ? $b : isset($c)) ? $c : "";
}

?>
