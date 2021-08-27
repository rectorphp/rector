<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\Astral\ValueObject\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
final class ScopeTypes
{
    /**
     * @var array<class-string<Node>>
     */
    public const STMT_TYPES = [\PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\Stmt\For_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class];
}
