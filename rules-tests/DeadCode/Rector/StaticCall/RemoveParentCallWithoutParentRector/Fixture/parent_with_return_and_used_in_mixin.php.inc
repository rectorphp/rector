<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class ParentWithReturnAndUsedInMixin extends SomeClass
{
    public function getIncrementing()
    {
        return parent::getIncrementing();
    }
}

class SomeClass
{
    public function getIncrementing()
    {
        return true;
    }
}

/**
 * @mixin SomeClass
 */
class SomeUnrelatedClass
{
}
