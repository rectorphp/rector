<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class A extends B
{
    protected function run()
    {
       return parent::run();
    }
}
