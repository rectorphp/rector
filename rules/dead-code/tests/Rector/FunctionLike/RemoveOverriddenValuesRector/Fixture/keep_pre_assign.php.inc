<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;

final class KeepPreAssign
{
    public function keep(Foreach_ $foreachNode)
    {
        $innerNode = $foreachNode->stmts[0];
        $innerNode = $innerNode instanceof Expression ? $innerNode->expr : $innerNode;

        return $innerNode;
    }
}
