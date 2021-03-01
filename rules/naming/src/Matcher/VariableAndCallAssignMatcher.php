<?php

declare(strict_types=1);

namespace Rector\Naming\Matcher;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\ValueObject\VariableAndCallAssign;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VariableAndCallAssignMatcher
{
    /**
     * @var CallMatcher
     */
    private $callMatcher;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(CallMatcher $callMatcher, NodeNameResolver $nodeNameResolver)
    {
        $this->callMatcher = $callMatcher;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function match(Assign $assign): ?VariableAndCallAssign
    {
        $call = $this->callMatcher->matchCall($assign);
        if ($call === null) {
            return null;
        }

        if (! $assign->var instanceof Variable) {
            return null;
        }

        $variableName = $this->nodeNameResolver->getName($assign->var);
        if ($variableName === null) {
            return null;
        }

        $functionLike = $this->getFunctionLike($assign);
        if ($functionLike === null) {
            return null;
        }

        return new VariableAndCallAssign($assign->var, $call, $assign, $variableName, $functionLike);
    }

    /**
     * @return ClassMethod|Function_|Closure|null
     */
    private function getFunctionLike(Assign $assign): ?FunctionLike
    {
        return $assign->getAttribute(AttributeKey::CLOSURE_NODE) ??
            $assign->getAttribute(AttributeKey::METHOD_NODE) ??
            $assign->getAttribute(AttributeKey::FUNCTION_NODE);
    }
}
