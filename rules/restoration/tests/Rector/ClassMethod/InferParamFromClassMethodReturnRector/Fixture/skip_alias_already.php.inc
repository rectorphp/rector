<?php

namespace Rector\Restoration\Tests\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Fixture;

use Hoa\Protocol\Node\Node;
use PhpParser\Node\Expr\AssignOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Plus as BinaryPlus;
use PhpParser\Node\Scalar\String_;
use Rector\Restoration\Tests\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Source\SomeType;

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
