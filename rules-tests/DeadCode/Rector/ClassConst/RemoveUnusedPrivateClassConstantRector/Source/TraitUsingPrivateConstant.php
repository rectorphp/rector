<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Source;

trait TraitUsingPrivateConstant
{
    public function foo()
    {
        return self::SOME_CONSTANT;
    }
}
