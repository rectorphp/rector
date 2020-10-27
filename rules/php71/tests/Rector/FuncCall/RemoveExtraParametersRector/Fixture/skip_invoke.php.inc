<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use ReflectionClass;

class SkipInvoke
{
    public function run($traitMock)
    {
        $reflectionClass = new ReflectionClass('SomeClass');
        $method = $reflectionClass->getMethod('getSetItems');

        $method->invoke($traitMock, 'some-param', 4, 5, 6);
    }
}
