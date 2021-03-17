<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;

final class SkipClassStringKeyValue
{
    /**
     * Key = the winner
     * Array = the group of types matched
     *
     * @var array<class-string<Node>, array<class-string<Expr>>>
     */
    public const PRIORITY_TYPES = [
        BinaryOp::class => [BinaryOp::class, Expr::class],
    ];
}
