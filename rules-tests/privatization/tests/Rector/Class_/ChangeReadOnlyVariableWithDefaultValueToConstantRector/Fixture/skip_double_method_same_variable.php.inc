<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

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
