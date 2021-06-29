<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

function run()
{
    $x = function () {
        $args = \func_get_args();
        echo $args[0];
    };

    $x('a');
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

function run()
{
    $x = function (...$args) {
        echo $args[0];
    };

    $x('a');
}

?>
