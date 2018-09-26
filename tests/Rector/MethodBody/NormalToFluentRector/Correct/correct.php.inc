<?php

namespace Rector\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction();
    }
}
