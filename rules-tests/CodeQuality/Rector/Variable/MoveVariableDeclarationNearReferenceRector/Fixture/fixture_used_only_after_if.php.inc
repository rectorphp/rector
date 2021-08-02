<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class FixtureUsedOnlyAfterIf
{
    function myMethod()
    {
        $var = 1;
        if (mktime() === false) {
            null;
        }
        return $var;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class FixtureUsedOnlyAfterIf
{
    function myMethod()
    {
        if (mktime() === false) {
            null;
        }
        $var = 1;
        return $var;
    }
}

?>
