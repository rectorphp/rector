<?php

namespace Rector\Tests\Rector\StaticCall\StaticCallToFunctionRector\Wrong;

use Rector\Tests\Rector\StaticCall\StaticCallToFunctionRector\Source\SomeOldStaticClass;

class SomeClass
{
    public function someMethod()
    {
        \view('template', []);
    }
}
