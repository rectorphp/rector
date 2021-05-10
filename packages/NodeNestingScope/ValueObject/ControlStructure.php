<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
final class ControlStructure
{
    /**
     * @var array<class-string<FunctionLike>>
     */
    public const RETURN_ISOLATING_SCOPE_NODE_TYPES = [\PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Expr\ArrowFunction::class];
    /**
     * @var array<class-string<Node>>
     */
    public const BREAKING_SCOPE_NODE_TYPES = [\PhpParser\Node\Stmt\For_::class, \PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\Do_::class, \PhpParser\Node\Stmt\Else_::class, \PhpParser\Node\Stmt\ElseIf_::class, \PhpParser\Node\Stmt\Catch_::class, \PhpParser\Node\Stmt\Case_::class, \PhpParser\Node\FunctionLike::class];
    /**
     * These situations happens only if condition is met
     * @var array<class-string<Node>>
     */
    public const CONDITIONAL_NODE_SCOPE_TYPES = [\PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\Do_::class, \PhpParser\Node\Stmt\Else_::class, \PhpParser\Node\Stmt\ElseIf_::class, \PhpParser\Node\Stmt\Catch_::class, \PhpParser\Node\Stmt\Case_::class, \PhpParser\Node\Expr\Match_::class, \PhpParser\Node\Stmt\Switch_::class, \PhpParser\Node\Stmt\Foreach_::class];
}
