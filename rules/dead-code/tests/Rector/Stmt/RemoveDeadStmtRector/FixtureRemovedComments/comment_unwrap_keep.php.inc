<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\ArrayDimFetch;

function commentUnwrapKeep()
{
    //comment
    $var[methodCall()];
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\ArrayDimFetch;

function commentUnwrapKeep()
{
    //comment
    methodCall();
}

?>
