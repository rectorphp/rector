<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

final class SomePropertyAssign
{
    public function run()
    {
        $this->someProperty = \func_get_args();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

final class SomePropertyAssign
{
    public function run(...$args)
    {
        $this->someProperty = $args;
    }
}

?>
