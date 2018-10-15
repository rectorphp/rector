<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Read-only utils for MethodCall Node:
 * "$this->someMethod()"
 */
final class MethodCallAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var CallAnalyzer
     */
    private $callAnalyzer;

    public function __construct(NodeTypeResolver $nodeTypeResolver, CallAnalyzer $callAnalyzer)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->callAnalyzer = $callAnalyzer;
    }

    /**
     * Checks "$this->classOfSpecificType->specificMethodName()"
     *
     * @param string[] $methods
     */
    public function isTypeAndMethods(Node $node, string $type, array $methods): bool
    {
        if (! $this->isTypes($node, [$type])) {
            return false;
        }

        return $this->callAnalyzer->isNames($node, $methods);
    }

    /**
     * Checks "$this->classOfSpecificType->specificMethodName()"
     */
    public function isTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isTypes($node, [$type])) {
            return false;
        }

        return $this->callAnalyzer->isName($node, $method);
    }

    /**
     * Checks "$this->specificNameMethod()"
     */
    public function isMethod(Node $node, string $methodName): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        return $this->callAnalyzer->isName($node, $methodName);
    }

    /**
     * @param string[] $methods
     */
    public function isMethods(Node $node, array $methods): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        return $this->callAnalyzer->isNames($node, $methods);
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

        $nodeTypes = $this->nodeTypeResolver->resolve($node->var);

        return array_intersect($nodeTypes, $types) ? $nodeTypes : null;
    }

    /**
     * @param string[] $methodNames
     */
    public function isThisMethodCallWithNames(Node $node, array $methodNames): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        if ($node->var->name !== 'this') {
            return false;
        }

        return $this->callAnalyzer->isNames($node, $methodNames);
    }

    /**
     * Matches:
     * - "$<variableName>-><methodName>()"
     */
    public function isMethodCallNameAndVariableName(Node $node, string $methodName, string $variableName): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        if (! $this->callAnalyzer->isName($node, $methodName)) {
            return false;
        }

        return $node->var->name === $variableName;
    }

    /**
     * @param string[] $types
     */
    public function isTypes(Node $node, array $types): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $calledNodeTypes = $this->nodeTypeResolver->resolve($node->var);

        return (bool) array_intersect($types, $calledNodeTypes);
    }
}
