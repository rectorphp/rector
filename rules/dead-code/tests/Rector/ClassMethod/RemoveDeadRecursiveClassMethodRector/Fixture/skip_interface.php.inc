<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

interface SkipInterface
{
    public function process();
}

class ImplementerOfTheInterface implements SkipInterface
{
    public function process()
    {
        $this->process();
    }
}
