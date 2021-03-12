<?php

namespace Rector\Tests\Arguments\Rector\FuncCall\SwapFuncCallArgumentsRector\Fixture;

final class Fixture
{
    public function run($one, $two)
    {
        return some_function($one, $two);
    }
}

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\FuncCall\SwapFuncCallArgumentsRector\Fixture;

final class Fixture
{
    public function run($one, $two)
    {
        return some_function($two, $one);
    }
}

?>
