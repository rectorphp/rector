<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

function InnerFunction()
{
    function theInner() {
        $args = \func_get_args();
        echo $args[0];
    };
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

function InnerFunction()
{
    function theInner(...$args) {
        echo $args[0];
    };
}

?>
