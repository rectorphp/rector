<?php

namespace Rector\Tests\CodeQuality\Rector\Stmt\DeadCodeRemovingRector\Fixture\ArrayDimFetch;

function commentUnwrapKeep()
{
    //comment
    $var[methodCall()];
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Stmt\DeadCodeRemovingRector\Fixture\ArrayDimFetch;

function commentUnwrapKeep()
{
    //comment
    methodCall();
}

?>
