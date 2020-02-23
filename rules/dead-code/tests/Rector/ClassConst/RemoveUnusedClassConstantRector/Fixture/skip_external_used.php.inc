<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class SkipExternalUsed
{
    public const SOME_CONST = 'dead';
}

class SomeUsed
{
    public function run()
    {
        return SkipExternalUsed::SOME_CONST;
    }
}
