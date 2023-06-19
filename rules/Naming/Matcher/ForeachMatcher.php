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
final class ForeachMatcher
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Matcher\CallMatcher
     */
    private $callMatcher;
    public function __construct(NodeNameResolver $nodeNameResolver, \Rector\Naming\Matcher\CallMatcher $callMatcher)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callMatcher = $callMatcher;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function match(Foreach_ $foreach, $functionLike) : ?VariableAndCallForeach
    {
        $call = $this->callMatcher->matchCall($foreach);
        if ($call === null) {
            return null;
        }
        if (!$foreach->valueVar instanceof Variable) {
            return null;
        }
        $variableName = $this->nodeNameResolver->getName($foreach->valueVar);
        if ($variableName === null) {
            return null;
        }
        return new VariableAndCallForeach($foreach->valueVar, $call, $variableName, $functionLike);
    }
}
