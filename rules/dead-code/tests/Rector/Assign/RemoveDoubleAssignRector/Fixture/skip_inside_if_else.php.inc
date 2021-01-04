<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\Fixture;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

class SkipInsideIfElse
{
    public function callMe(StaticCall $staticCall, $newMethod): StaticCall
    {
        if (is_array($newMethod)) {
            [$newClass, $newMethod] = $newMethod;
            $staticCall->class = new Name($newClass);
            $staticCall->name = new Identifier($newMethod);
        } else {
            $staticCall->name = new Identifier($newMethod);
        }

        return $staticCall;
    }
}
