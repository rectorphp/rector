<?php

namespace Rector\Core\Tests\Rector\Argument\SwapFuncCallArgumentsRector\Fixture;

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

namespace Rector\Core\Tests\Rector\Argument\SwapFuncCallArgumentsRector\Fixture;

final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($two, $one);
    }
}

?>
