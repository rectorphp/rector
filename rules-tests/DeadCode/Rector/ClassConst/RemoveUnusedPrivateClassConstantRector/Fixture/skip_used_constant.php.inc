<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

final class SkipUsedConstant
{
    private const SOME_CONST = 'dead';

    public function run()
    {
        return self::SOME_CONST;
    }
}
