<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

class SkipExistingParent extends ParentClassWithProtectedMethod
{
    protected function run()
    {
    }
}

class ParentClassWithProtectedMethod
{
    protected function run()
    {
    }
}
