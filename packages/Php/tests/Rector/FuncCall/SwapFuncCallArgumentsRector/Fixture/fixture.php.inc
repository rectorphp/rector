<?php

namespace Rector\Php\Tests\Rector\FuncCall\SwapFuncCallArgumentsRector\Fixture;

final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($one, $two);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\SwapFuncCallArgumentsRector\Fixture;

final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($two, $one);
    }
}

?>
