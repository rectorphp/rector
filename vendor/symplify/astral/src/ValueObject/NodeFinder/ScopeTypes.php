<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\Astral\ValueObject\NodeFinder;

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
    public const STMT_TYPES = [If_::class, Foreach_::class, For_::class, While_::class, ClassMethod::class, Function_::class, Closure::class];
}
