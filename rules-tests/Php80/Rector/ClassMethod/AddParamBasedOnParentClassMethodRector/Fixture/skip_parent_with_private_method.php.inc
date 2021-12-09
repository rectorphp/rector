<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source\ParentWithPrivateMethod;

class SkipParentWithPrivateMethod extends ParentWithPrivateMethod
{
    public function execute()
    {
    }
}
