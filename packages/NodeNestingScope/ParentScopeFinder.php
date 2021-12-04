<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Namespace_|null
     */
    public function find(\PhpParser\Node $node)
    {
        return $this->betterNodeFinder->findParentByTypes($node, [\PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Namespace_::class]);
    }
}
