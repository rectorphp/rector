<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Clone_;

function wrapToPreventPhpStanCallingMethods ($object)
{
    $a = clone $object;
    clone $object;
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Clone_;

function wrapToPreventPhpStanCallingMethods ($object)
{
    $a = clone $object;
}
?>
