<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

function notLastArgument2($a = false, $b = false, $c = false, $d = false) {
    $level = getLevelFromSomewhere();

    if ($level == "a" && !$a) return false;
    if ($level == "b" && !$b) return false;
    if ($level == "c" && !$c) return false;
    if ($level == "d" && !$d) return false;
}

notLastArgument2(false, false, true, false);

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

function notLastArgument2($a = false, $b = false, $c = false, $d = false) {
    $level = getLevelFromSomewhere();

    if ($level == "a" && !$a) return false;
    if ($level == "b" && !$b) return false;
    if ($level == "c" && !$c) return false;
    if ($level == "d" && !$d) return false;
}

notLastArgument2(false, false, true);

?>
