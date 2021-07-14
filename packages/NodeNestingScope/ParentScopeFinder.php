<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;

final class ParentScopeFinder
{
    public function __construct(
        private ParentFinder $parentFinder
    ) {
    }

    public function find(Node $node): ClassMethod | Function_ | Class_ | Namespace_ | Closure | null
    {
        return $this->parentFinder->findByTypes($node, [
            Closure::class,
            Function_::class,
            ClassMethod::class,
            Class_::class,
            Namespace_::class,
        ]);
    }
}
