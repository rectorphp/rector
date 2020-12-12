<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\ClassConstantFetch;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = SomeClass::class;

    SomeClass::class;

    bar()::FOO;
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\ClassConstantFetch;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = SomeClass::class;

    bar();
}
?>
