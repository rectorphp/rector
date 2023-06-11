<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
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
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Namespace_|\PhpParser\Node\Expr\Closure|null
     */
    public function find(Variable $variable)
    {
        return $this->betterNodeFinder->findParentByTypes($variable, [Closure::class, Function_::class, ClassMethod::class, Class_::class, Namespace_::class]);
    }
}
