<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ChildOfToBeImplementedInterface;
use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ParentClassWithInterfaceType;

class SkipParentDifferentType extends ParentClassWithInterfaceType
{
    public function __construct(ChildOfToBeImplementedInterface $toBeImplemented)
    {
        parent::__construct($toBeImplemented);
    }
}
