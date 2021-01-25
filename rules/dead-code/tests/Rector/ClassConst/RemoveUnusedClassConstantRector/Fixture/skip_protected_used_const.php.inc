<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class UserConstant extends SkipProtectedUsedConst
{
    public function run()
    {
        return self::USED_CONSTANT;
    }
}

abstract class SkipProtectedUsedConst
{
    protected const USED_CONSTANT = 'yes';
}
