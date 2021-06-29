<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class ParentScopeFinder
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function find(Node $node): ClassMethod | Function_ | Class_ | Namespace_ | Closure | null
    {
        return $this->betterNodeFinder->findParentTypes($node, [
            Closure::class,
            Function_::class,
            ClassMethod::class,
            Class_::class,
            Namespace_::class,
        ]);
    }
}
