<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector\Fixture;

final class SkipStringArray
{
    public function run()
    {
        $result = \call_user_func(['some', 'method'], $args);
    }
}
