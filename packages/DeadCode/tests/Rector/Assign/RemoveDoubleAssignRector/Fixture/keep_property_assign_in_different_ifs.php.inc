<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\Fixture;

use PhpParser\Node\Expr;

class KeepPropertyAssignInDifferentIfs
{
    /**
     * @var bool
     */
    private $isBoolAssert = false;

    public function resolveBoolMethodName(string $name): string
    {
        if ($name === 'assertSame') {
            $this->isBoolAssert = true;
        }

        if ($name === 'assertNotSame') {
            $this->isBoolAssert = true;
        }

        return $this->isBoolAssert;
    }
}
