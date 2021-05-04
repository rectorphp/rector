<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector\Fixture;

final class SimpleClosure
{
    public function run()
    {
        $result = \call_user_func(function () {
            return 100;
        });
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector\Fixture;

final class SimpleClosure
{
    public function run()
    {
        $result = 100;
    }
}

?>
