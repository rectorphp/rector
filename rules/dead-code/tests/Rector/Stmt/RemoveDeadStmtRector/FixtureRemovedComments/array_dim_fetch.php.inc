<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\ArrayDimFetch;

function wrapToPreventPhpStanCallingMethods ()
{
    $var[0] = 1;

    $var[0];

    //comment
    $var[methodCall()];

    //comment
    ${methodCall()}[0];

    //comment
    ${methodCall1()}[methodCall2()];
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\ArrayDimFetch;

function wrapToPreventPhpStanCallingMethods ()
{
    $var[0] = 1;

    //comment
    methodCall();

    //comment
    methodCall();

    //comment
    methodCall1();
    methodCall2();
}
?>
