<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeTypeResolver\ScopeToTypesResolver;

/**
 * Read-only utils for MethodCall Node:
 * "$this->someMethod()"
 */
final class MethodCallAnalyzer
{
    /**
     * @var ScopeToTypesResolver
     */
    private $scopeToTypesResolver;

    public function __construct(ScopeToTypesResolver $scopeToTypesResolver)
    {
        $this->scopeToTypesResolver = $scopeToTypesResolver;
    }

    /**
     * Checks "$this->classOfSpecificType->specificMethodName()"
     *
     * @param string[] $methods
     */
    public function isTypeAndMethods(Node $node, string $type, array $methods): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var Identifier $methodCallNode */
        $methodName = $node->name;

        return in_array($methodName->toString(), $methods, true);
    }

    /**
     * Checks "$this->classOfSpecificType->specificMethodName()"
     */
    public function isTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var Identifier $methodName */
        $methodName = $node->name;

        return $methodName->toString() === $method;
    }

    /**
     * Checks "$this->specificNameMethod()"
     */
    public function isMethod(Node $node, string $methodName): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        return $node->name->name === $methodName;
    }

    /**
     * @param string[] $methods
     */
    public function isMethods(Node $node, array $methods): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        return in_array($node->name->name, $methods, true);
    }

    /**
     * Checks "$this->methodCall()"
     */
    public function isType(Node $node, string $type): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $calledNodeTypes2 = $this->scopeToTypesResolver->resolveScopeToTypes($node->var);

        return in_array($type, $calledNodeTypes2, true);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    public function matchTypes(Node $node, array $types): ?array
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        $nodeTypes = $this->scopeToTypesResolver->resolveScopeToTypes($node->var);

        return array_intersect($nodeTypes, $types) ? $nodeTypes : null;
    }
}
