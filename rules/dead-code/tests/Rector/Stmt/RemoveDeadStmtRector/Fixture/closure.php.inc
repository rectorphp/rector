<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Closure;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = function () {};
    function () {};
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Closure;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = function () {};
}
?>
