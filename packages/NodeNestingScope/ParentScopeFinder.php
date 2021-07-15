<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
final class ParentScopeFinder
{
    /**
     * @var \Rector\NodeNestingScope\ParentFinder
     */
    private $parentFinder;
    public function __construct(\Rector\NodeNestingScope\ParentFinder $parentFinder)
    {
        $this->parentFinder = $parentFinder;
    }
    /**
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Namespace_|\PhpParser\Node\Expr\Closure|null
     */
    public function find(\PhpParser\Node $node)
    {
        return $this->parentFinder->findByTypes($node, [\PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Namespace_::class]);
    }
}
