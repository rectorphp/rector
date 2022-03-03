<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipConstant
{
    public function run()
    {
        $loopNodes = SOME_CONSTANT;
        foreach ($loopNodes as $loopNode) {

        }
    }
}
