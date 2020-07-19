<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

function testArrowFunction() {
    $datetime = new DateTime();

    $fn1 = fn($x) => $x + $stamp = $datetime->getTimestamp();
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

function testArrowFunction() {
    $datetime = new DateTime();

    $fn1 = fn($x) => $x + $timestamp = $datetime->getTimestamp();
}

?>
