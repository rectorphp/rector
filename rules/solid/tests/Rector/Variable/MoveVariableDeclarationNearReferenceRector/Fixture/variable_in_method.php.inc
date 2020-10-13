<?php

namespace Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class VariableInMethod
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

class VariableInMethod
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
