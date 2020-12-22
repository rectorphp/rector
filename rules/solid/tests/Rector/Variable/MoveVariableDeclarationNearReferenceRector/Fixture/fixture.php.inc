<?php

namespace Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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

namespace Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
