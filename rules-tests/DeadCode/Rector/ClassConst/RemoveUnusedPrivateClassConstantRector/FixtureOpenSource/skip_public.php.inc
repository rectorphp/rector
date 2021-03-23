<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

class SkipPublic
{
    public const SOME_CONST = 'dead';
}
