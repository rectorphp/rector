<?php

declare (strict_types=1);
namespace Rector\Naming\Matcher;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\ValueObject\VariableAndCallForeach;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ParentFinder;
final class ForeachMatcher
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Naming\Matcher\CallMatcher
     */
    private $callMatcher;
    /**
     * @var \Rector\NodeNestingScope\ParentFinder
     */
    private $parentFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Naming\Matcher\CallMatcher $callMatcher, \Rector\NodeNestingScope\ParentFinder $parentFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callMatcher = $callMatcher;
        $this->parentFinder = $parentFinder;
    }
    public function match(\PhpParser\Node\Stmt\Foreach_ $foreach) : ?\Rector\Naming\ValueObject\VariableAndCallForeach
    {
        $call = $this->callMatcher->matchCall($foreach);
        if ($call === null) {
            return null;
        }
        if (!$foreach->valueVar instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $functionLike = $this->getFunctionLike($foreach);
        if ($functionLike === null) {
            return null;
        }
        $variableName = $this->nodeNameResolver->getName($foreach->valueVar);
        if ($variableName === null) {
            return null;
        }
        return new \Rector\Naming\ValueObject\VariableAndCallForeach($foreach->valueVar, $call, $variableName, $functionLike);
    }
    /**
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|null
     */
    private function getFunctionLike(\PhpParser\Node\Stmt\Foreach_ $foreach)
    {
        return $this->parentFinder->findByTypes($foreach, [\PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class]);
    }
}
