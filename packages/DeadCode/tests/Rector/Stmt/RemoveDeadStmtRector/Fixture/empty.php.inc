<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Empty_;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = empty($a);
    empty($a);
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Empty_;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = empty($a);
}
?>
