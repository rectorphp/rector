<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Source\MyException;

class StaticCallNextException
{
    function myMethod()
    {
        $var = do_something();
        if (mktime() === false) {
            throw MyException::notFound();
        }
        echo $var;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Source\MyException;

class StaticCallNextException
{
    function myMethod()
    {
        if (mktime() === false) {
            throw MyException::notFound();
        }
        $var = do_something();
        echo $var;
    }
}

?>
