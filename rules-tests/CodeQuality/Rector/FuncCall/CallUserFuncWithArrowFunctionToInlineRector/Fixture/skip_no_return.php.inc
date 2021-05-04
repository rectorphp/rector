<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector\Fixture;

final class SkipNoReturn
{
    public function run()
    {
        $result = \call_user_func(function () {
            echo 1000;
        });
    }
}
