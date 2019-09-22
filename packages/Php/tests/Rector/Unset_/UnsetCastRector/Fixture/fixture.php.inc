<?php

namespace Rector\Php\Tests\Rector\Unset_\UnsetCastRector\Fixture;

function unsetCast()
{
    $value = (unset)$value;

    (unset) strlen('hi');
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\Unset_\UnsetCastRector\Fixture;

function unsetCast()
{
    $value = null;

    null;
}

?>
