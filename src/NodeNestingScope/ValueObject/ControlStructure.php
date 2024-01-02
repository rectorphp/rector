<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
final class ControlStructure
{
    /**
     * These situations happens only if condition is met
     * @var array<class-string<Node>>
     */
    public const CONDITIONAL_NODE_SCOPE_TYPES = [If_::class, While_::class, Do_::class, Else_::class, ElseIf_::class, Catch_::class, Case_::class, Match_::class, Switch_::class, Foreach_::class];
}
