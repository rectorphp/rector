<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector\Fixture;

final class SkipUsedInTrait
{
    use aTrait;
    private const SOME_CONSTANT = 5;

}

trait aTrait {
    public function foo(){
        return self::SOME_CONSTANT;
    }
}
