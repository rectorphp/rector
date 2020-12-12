<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\StaticPropertyFetch;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = SomeClass::$a;

    SomeClass::$a;

    bar()::$a;

    bar()::${baz()}::${foo()};
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\StaticPropertyFetch;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = SomeClass::$a;

    bar();

    bar();
    baz();
    foo();
}
?>
