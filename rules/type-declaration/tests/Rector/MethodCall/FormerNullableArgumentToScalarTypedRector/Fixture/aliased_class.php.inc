<?php

namespace Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Bar\Baz as BarBaz;

class FooBarBaz
{
    public function doSomething()
    {
        $barBaz = new BarBaz();
        return $barBaz->process('someValue');
    }
}
