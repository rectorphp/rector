<?php

declare(strict_types=1);

namespace Rector\Naming\Matcher;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
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
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function match(Assign $assign): ?VariableAndCallAssign
    {
        $call = $this->matchCall($assign);
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
    private function getFunctionLike(Node $node): ?FunctionLike
    {
        return $node->getAttribute(AttributeKey::CLOSURE_NODE) ??
            $node->getAttribute(AttributeKey::METHOD_NODE) ??
            $node->getAttribute(AttributeKey::FUNCTION_NODE);
    }

    /**
     * @return FuncCall|StaticCall|MethodCall|null
     */
    private function matchCall(Assign $assign): ?Node
    {
        if ($assign->expr instanceof MethodCall) {
            return $assign->expr;
        }

        if ($assign->expr instanceof StaticCall) {
            return $assign->expr;
        }

        if ($assign->expr instanceof FuncCall) {
            return $assign->expr;
        }

        return null;
    }
}
