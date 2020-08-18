<?php

namespace Rector\CodeQuality\Tests\Rector\Expression\DeadCodeRemovingRector\Fixture\Empty_;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = empty($a);
    empty($a);
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Expression\DeadCodeRemovingRector\Fixture\Empty_;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = empty($a);
}
?>
