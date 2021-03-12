<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

class SkipAccessOverride extends ParentClassWithProtected
{
    public function getName()
    {
        return parent::getName();
    }
}

class ParentClassWithProtected
{
    protected function getName()
    {
        return 'hello';
    }
}
