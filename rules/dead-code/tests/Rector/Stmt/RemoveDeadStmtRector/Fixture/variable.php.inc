<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Variable;

function wrapToPreventPhpStanCallingMethods()
{
    $a;

    //keep comment
    ${methodCall()};
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\Variable;

function wrapToPreventPhpStanCallingMethods()
{
    //keep comment
    methodCall();
}

?>
