<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector\Fixture;

final class SkipStringVariable
{
    public function run()
    {
        $classname = 'some';
        call_user_func([$classname, 'method']);
        $result = call_user_func(['some', 'method'], $args);
    }
}
