<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class SkipChildUsed
{
    protected const SOME_CONST = 'dead';
}

class SomeChild extends SkipChildUsed
{
    public function run()
    {
        return self::SOME_CONST;
    }
}
