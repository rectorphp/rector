<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Unairy;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = -$a;
    $a = +$a;
    $a = !$a;
    $a = ~$a;

    -$a;
    +$a;
    !$a;
    ~$a;
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Unairy;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = -$a;
    $a = +$a;
    $a = !$a;
    $a = ~$a;
}
?>
