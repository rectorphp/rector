<?php

declare (strict_types=1);
namespace Rector\Naming\Matcher;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\ValueObject\VariableAndCallForeach;
use Rector\NodeNameResolver\NodeNameResolver;
final class ForeachMatcher
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var CallMatcher
     */
    private $callMatcher;
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Naming\Matcher\CallMatcher $callMatcher, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callMatcher = $callMatcher;
        $this->betterNodeFinder = $betterNodeFinder;
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
     * @return ClassMethod|Function_|Closure|null
     */
    private function getFunctionLike(\PhpParser\Node\Stmt\Foreach_ $foreach) : ?\PhpParser\Node
    {
        return $this->betterNodeFinder->findParentTypes($foreach, [\PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class]);
    }
}
