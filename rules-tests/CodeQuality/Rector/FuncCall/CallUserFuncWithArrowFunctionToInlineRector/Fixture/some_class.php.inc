<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $result = \call_user_func(fn () => 100);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $result = 100;
    }
}

?>
