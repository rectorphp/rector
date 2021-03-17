<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\DeadCodeRemovingRector\Fixture\ArrayDimFetch;

function commentUnwrapKeep()
{
    //comment
    $var[methodCall()];
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\DeadCodeRemovingRector\Fixture\ArrayDimFetch;

function commentUnwrapKeep()
{
    //comment
    methodCall();
}

?>
