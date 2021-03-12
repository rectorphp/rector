<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\FixturePhp74;

function run()
{
    $a = 1;
    $func = fn($b) => $a + $b + $c;

    return $func(2);
}

?>
-----
<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\FixturePhp74;

function run()
{
    $c = null;
    $a = 1;
    $func = fn($b) => $a + $b + $c;

    return $func(2);
}

?>
