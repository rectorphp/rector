<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipDoubleMethodSameVariable
{
    public function run()
    {
        $method = 'you';

        return $method;
    }

    public function go()
    {
        $method = 'me';

        return $method;
    }
}
