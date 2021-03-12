<?php

namespace Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Source\ClassMethodWithCall;

final class SkipIfConditionMethodCallReturnBool
{
    public function run(ClassMethodWithCall $classMethodWithCall, $arg)
    {
        if ($classMethodWithCall->condition($arg)) {

        }
    }
}

