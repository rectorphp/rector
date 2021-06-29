<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

function run()
{
    $args0 = \func_get_args();
    $x = function () {
        $args1 = \func_get_args();
        echo $args1[0];
    };

    $x('a');
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

function run(...$args0)
{
    $x = function (...$args1) {
        echo $args1[0];
    };

    $x('a');
}

?>
