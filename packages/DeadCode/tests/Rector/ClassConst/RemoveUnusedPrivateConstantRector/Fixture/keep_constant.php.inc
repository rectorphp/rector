<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedPrivateConstantRector\Fixture;

final class KeepConstant
{
    private const SOME_CONSTANT = 5;
    public function run()
    {
        return self::SOME_CONSTANT;
    }
}
