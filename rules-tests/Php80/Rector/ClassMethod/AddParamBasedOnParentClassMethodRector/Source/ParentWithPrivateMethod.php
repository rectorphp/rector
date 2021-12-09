<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source;

class ParentWithPrivateMethod
{
    private function execute($foo)
    {
    }
}
