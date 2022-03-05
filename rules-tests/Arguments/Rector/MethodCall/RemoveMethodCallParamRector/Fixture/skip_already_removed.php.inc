<?php

namespace Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Fixture;

use Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Source\MethodCaller;

final class SkipAlreadyRemoved
{
    public function run(MethodCaller $caller)
    {
        $caller->process(1);
    }
}
