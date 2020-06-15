<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipDoubleAssign
{
    protected function run()
    {
        $a = $b = $c = $d = '';
    }
}
