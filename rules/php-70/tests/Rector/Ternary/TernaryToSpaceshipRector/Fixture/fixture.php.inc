<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToSpaceshipRector\Fixture;

function order_func($a, $b) {
    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);

    return ($a > $b) ? -1 : (($a < $b) ? 1 : 0);
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToSpaceshipRector\Fixture;

function order_func($a, $b) {
    return $a <=> $b;

    return $b <=> $a;
}

?>
