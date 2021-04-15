<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

final class KeepConstant
{
    private const SOME_CONSTANT = 5;
    public function run()
    {
        return self::SOME_CONSTANT;
    }
}
