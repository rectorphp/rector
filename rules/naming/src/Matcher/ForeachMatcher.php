<?php

declare(strict_types=1);

namespace Rector\Naming\Matcher;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\ValueObject\VariableAndCallForeach;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

    public function __construct(NodeNameResolver $nodeNameResolver, CallMatcher $callMatcher)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callMatcher = $callMatcher;
    }

    public function match(Foreach_ $foreach): ?VariableAndCallForeach
    {
        $call = $this->callMatcher->matchCall($foreach);
        if ($call === null) {
            return null;
        }

        if (! $foreach->valueVar instanceof Variable) {
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

        return new VariableAndCallForeach($foreach->valueVar, $call, $variableName, $functionLike);
    }

    /**
     * @return ClassMethod|Function_|Closure|null
     */
    private function getFunctionLike(Foreach_ $foreach): ?FunctionLike
    {
        return $foreach->getAttribute(AttributeKey::CLOSURE_NODE) ??
            $foreach->getAttribute(AttributeKey::METHOD_NODE) ??
            $foreach->getAttribute(AttributeKey::FUNCTION_NODE);
    }
}
