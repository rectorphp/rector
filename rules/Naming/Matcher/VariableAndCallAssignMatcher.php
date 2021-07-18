<?php

declare (strict_types=1);
namespace Rector\Naming\Matcher;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\ValueObject\VariableAndCallAssign;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ParentFinder;
final class VariableAndCallAssignMatcher
{
    /**
     * @var \Rector\Naming\Matcher\CallMatcher
     */
    private $callMatcher;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeNestingScope\ParentFinder
     */
    private $parentFinder;
    public function __construct(\Rector\Naming\Matcher\CallMatcher $callMatcher, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeNestingScope\ParentFinder $parentFinder)
    {
        $this->callMatcher = $callMatcher;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parentFinder = $parentFinder;
    }
    public function match(\PhpParser\Node\Expr\Assign $assign) : ?\Rector\Naming\ValueObject\VariableAndCallAssign
    {
        $call = $this->callMatcher->matchCall($assign);
        if ($call === null) {
            return null;
        }
        if (!$assign->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $variableName = $this->nodeNameResolver->getName($assign->var);
        if ($variableName === null) {
            return null;
        }
        $functionLike = $this->getFunctionLike($assign);
        if (!$functionLike instanceof \PhpParser\Node\FunctionLike) {
            return null;
        }
        return new \Rector\Naming\ValueObject\VariableAndCallAssign($assign->var, $call, $assign, $variableName, $functionLike);
    }
    /**
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|null
     */
    private function getFunctionLike(\PhpParser\Node\Expr\Assign $assign)
    {
        return $this->parentFinder->findByTypes($assign, [\PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class]);
    }
}
