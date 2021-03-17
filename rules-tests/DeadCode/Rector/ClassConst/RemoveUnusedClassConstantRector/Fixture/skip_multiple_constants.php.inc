<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class SkipMultipleConstants
{
    public const SOME_CONST = 'dead', NOT_USED_EITHER = 'dead';
}
