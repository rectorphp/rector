<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeNestingScope\ValueObject;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrowFunction;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\Match_;
use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\Case_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Catch_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Do_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Else_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ElseIf_;
use RectorPrefix20220606\PhpParser\Node\Stmt\For_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Foreach_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Switch_;
use RectorPrefix20220606\PhpParser\Node\Stmt\While_;
final class ControlStructure
{
    /**
     * @var array<class-string<FunctionLike>>
     */
    public const RETURN_ISOLATING_SCOPE_NODE_TYPES = [Function_::class, ClassMethod::class, Closure::class, ArrowFunction::class];
    /**
     * @var array<class-string<Node>>
     */
    public const BREAKING_SCOPE_NODE_TYPES = [For_::class, Foreach_::class, If_::class, While_::class, Do_::class, Else_::class, ElseIf_::class, Catch_::class, Case_::class, FunctionLike::class];
    /**
     * These situations happens only if condition is met
     * @var array<class-string<Node>>
     */
    public const CONDITIONAL_NODE_SCOPE_TYPES = [If_::class, While_::class, Do_::class, Else_::class, ElseIf_::class, Catch_::class, Case_::class, Match_::class, Switch_::class, Foreach_::class];
    /**
     * @var array<class-string<Node>>
     */
    public const LOOP_NODES = [For_::class, Foreach_::class, While_::class, Do_::class];
}
