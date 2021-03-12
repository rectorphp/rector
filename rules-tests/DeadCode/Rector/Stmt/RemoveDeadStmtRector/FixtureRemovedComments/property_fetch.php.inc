<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\PropertyFetch;

function wrapToPreventPhpStanCallingMethods ()
{
    $var->prop = 1;

    $var->prop;

    //comment
    $var->{methodCall()};

    //comment
    ${methodCall()}->prop;

    //comment
    ${methodCall1()}->{methodCall2()};
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\PropertyFetch;

function wrapToPreventPhpStanCallingMethods ()
{
    $var->prop = 1;

    //comment
    methodCall();

    //comment
    methodCall();

    //comment
    methodCall1();
    methodCall2();
}
?>
