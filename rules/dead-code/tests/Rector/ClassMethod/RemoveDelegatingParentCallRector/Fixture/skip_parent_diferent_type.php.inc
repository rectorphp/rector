<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ChildOfToBeImplementedInterface;
use Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ParentClassWithInterfaceType;

class SkipParentDifferentType extends ParentClassWithInterfaceType
{
    public function __construct(ChildOfToBeImplementedInterface $toBeImplemented)
    {
        parent::__construct($toBeImplemented);
    }
}
