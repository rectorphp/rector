<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\Fixture;

class SkipUsed
{
    public function run()
    {
        return $this->run();
    }

    public function skipUsed()
    {
        $this->run();
    }
}
