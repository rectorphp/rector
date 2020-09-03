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
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\ValueObject\VariableAndCallAssign;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractMatcher
{
    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    abstract public function getVariableName(Node $node): ?string;

    abstract public function getVariable(Node $node): Variable;

    abstract public function getAssign(Node $node): ?Assign;

    /**
     * @param Assign|Foreach_ $node
     */
    public function match(Node $node): ?VariableAndCallAssign
    {
        $call = $this->matchCall($node);
        if ($call === null) {
            return null;
        }

        $variableName = $this->getVariableName($node);
        if ($variableName === null) {
            return null;
        }

        $functionLike = $this->getFunctionLike($node);
        if ($functionLike === null) {
            return null;
        }

        return new VariableAndCallAssign($this->getVariable($node), $call, $this->getAssign(
            $node
        ), $variableName, $functionLike);
    }

    /**
     * @return FuncCall|StaticCall|MethodCall|null
     */
    protected function matchCall(Node $node): ?Node
    {
        if ($node->expr instanceof MethodCall) {
            return $node->expr;
        }

        if ($node->expr instanceof StaticCall) {
            return $node->expr;
        }

        if ($node->expr instanceof FuncCall) {
            return $node->expr;
        }

        return null;
    }

    /**
     * @return ClassMethod|Function_|Closure|null
     */
    protected function getFunctionLike(Node $node): ?FunctionLike
    {
        return $node->getAttribute(AttributeKey::CLOSURE_NODE) ??
            $node->getAttribute(AttributeKey::METHOD_NODE) ??
            $node->getAttribute(AttributeKey::FUNCTION_NODE);
    }
}
