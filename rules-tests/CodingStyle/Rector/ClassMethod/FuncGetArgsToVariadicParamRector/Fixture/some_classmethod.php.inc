<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

final class SomeClassMethod
{
    function run()
    {
        $args = \func_get_args();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

final class SomeClassMethod
{
    function run(...$args)
    {
    }
}

?>
