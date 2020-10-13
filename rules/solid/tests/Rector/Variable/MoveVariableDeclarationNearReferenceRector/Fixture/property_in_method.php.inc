<?php

namespace Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class PropertyInMethod
{
    private $var;
    function myMethod()
    {
        $this->var = 1;
        if (mktime() === false) {
            return $this->var;
        }
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class PropertyInMethod
{
    private $var;
    function myMethod()
    {
        $this->var = 1;
        if (mktime() === false) {
            return $this->var;
        }
    }
}

?>
