<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedPrivateConstantRector\Fixture;

final class UsedInTrait
{
    use aTrait;
    private const SOME_CONSTANT = 5;

}

trait aTrait {
    public function foo(){
        return self::SOME_CONSTANT;
    }
}
