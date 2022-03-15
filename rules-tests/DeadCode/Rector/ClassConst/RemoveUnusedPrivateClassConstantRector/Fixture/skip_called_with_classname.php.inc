<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

final class SkipCalledByClassName
{
    private const SOME_CONST = 1;

    public function run()
    {
        return SkipCalledByClassName::SOME_CONST;
    }
}

?>
