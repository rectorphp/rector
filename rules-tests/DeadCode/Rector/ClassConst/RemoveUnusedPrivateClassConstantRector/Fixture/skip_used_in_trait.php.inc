<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Source\TraitUsingPrivateConstant;

final class SkipUsedInTrait
{
    use TraitUsingPrivateConstant;

    private const SOME_CONSTANT = 5;
}
