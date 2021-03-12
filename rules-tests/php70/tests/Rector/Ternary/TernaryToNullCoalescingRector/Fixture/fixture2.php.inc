<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector\Fixture;

function ternaryToNullCoalescing2()
{
    $x = isset($a) ? $a : 1;
    $y = isset($b) ? "b" : 2;
    $x = isset($c) ? $c : 3;

    $x = isset($a) ? $a : (isset($b) ? $b : "");
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector\Fixture;

function ternaryToNullCoalescing2()
{
    $x = $a ?? 1;
    $y = isset($b) ? "b" : 2;
    $x = $c ?? 3;

    $x = $a ?? $b ?? "";
}

?>
