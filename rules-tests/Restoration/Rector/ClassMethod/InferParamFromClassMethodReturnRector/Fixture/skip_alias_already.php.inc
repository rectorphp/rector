<?php

namespace Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\AssignOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Plus as BinaryPlus;
use Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Source\SomeType;

class SkipAliasAlready extends SomeType
{
    public function getNodeTypes(): array
    {
        return [Plus::class, BinaryPlus::class];
    }

    /**
     * @param Plus|BinaryPlus $node
     */
    public function process(Node $node)
    {
    }
}
