<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

class SkipMultipleConstants
{
    private const SOME_CONST = 'dead', NOT_USED_EITHER = 'dead';
}
