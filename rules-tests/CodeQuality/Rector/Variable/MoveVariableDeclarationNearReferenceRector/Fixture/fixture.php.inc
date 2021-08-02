<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class Fixture
{
    function myMethod()
    {
        $var = 1;
        if (mktime() === false) {
            return $var;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class Fixture
{
    function myMethod()
    {
        if (mktime() === false) {
            $var = 1;
            return $var;
        }
    }
}

?>
