<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeNestingScope;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Namespace_;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
final class ParentScopeFinder
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Namespace_|\PhpParser\Node\Expr\Closure|null
     */
    public function find(Node $node)
    {
        return $this->betterNodeFinder->findParentByTypes($node, [Closure::class, Function_::class, ClassMethod::class, Class_::class, Namespace_::class]);
    }
}
