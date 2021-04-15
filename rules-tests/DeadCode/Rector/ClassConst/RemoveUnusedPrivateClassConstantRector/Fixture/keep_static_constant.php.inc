<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

final class KeepStaticConstant
{
    private const SOME_CONSTANT = 5;
    public function run()
    {
        return static::SOME_CONSTANT;
    }
}
