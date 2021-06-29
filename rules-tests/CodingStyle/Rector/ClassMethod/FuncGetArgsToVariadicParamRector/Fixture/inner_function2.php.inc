<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

function InnerFunction()
{
    $args0 = \func_get_args();
    function theInner() {
        $args1 = \func_get_args();
        echo $args1[0];
    };
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

function InnerFunction(...$args0)
{
    function theInner(...$args1) {
        echo $args1[0];
    };
}

?>
