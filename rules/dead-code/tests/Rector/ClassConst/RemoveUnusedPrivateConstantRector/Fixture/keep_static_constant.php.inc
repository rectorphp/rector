<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedPrivateConstantRector\Fixture;

final class KeepStaticConstant
{
    private const SOME_CONSTANT = 5;
    public function run()
    {
        return static::SOME_CONSTANT;
    }
}
