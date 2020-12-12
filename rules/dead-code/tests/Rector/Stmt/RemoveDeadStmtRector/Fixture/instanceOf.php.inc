<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\InstanceOf_;

function wrapToPreventPhpStanCallingMethods ()
{
    $b = $a instanceof \Exception;

    $a instanceof \Exception;

    foo1() instanceof \Exception;
    foo2() instanceof $className;
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Fixture\InstanceOf_;

function wrapToPreventPhpStanCallingMethods ()
{
    $b = $a instanceof \Exception;

    foo1();
    foo2();
}
?>
