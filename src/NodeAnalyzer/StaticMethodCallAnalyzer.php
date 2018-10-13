<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Read-only utils for StaticCall Node:
 * "SomeClass::someMethod()"
 */
final class StaticMethodCallAnalyzer
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
     * Checks "SpecificType::specificMethod()"
     */
    public function isTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var StaticCall $node */
        return $this->callAnalyzer->isName($node, $method);
    }

    /**
     * @param string[] $methods
     */
    public function isMethods(Node $node, array $methods): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        return in_array($this->callAnalyzer->resolveName($node), $methods, true);
    }

    public function isMethod(Node $node, string $method): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        return $this->callAnalyzer->resolveName($node) === $method;
    }

    /**
     * Checks "SpecificType::oneOfSpecificMethods()"
     *
     * @param string[] $methodNames
     */
    public function isTypeAndMethods(Node $node, string $type, array $methodNames): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var StaticCall $node */
        return $this->callAnalyzer->isNames($node, $methodNames);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    public function matchTypes(Node $node, array $types): ?array
    {
        if (! $node instanceof StaticCall) {
            return null;
        }

        $nodeTypes = $this->nodeTypeResolver->resolve($node->class);

        return array_intersect($nodeTypes, $types) ? $nodeTypes : null;
    }

    /**
     * Checks "SpecificType::anyMethod()"
     */
    private function isType(Node $node, string $type): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        $classTypes = $this->nodeTypeResolver->resolve($node->class);

        return in_array($type, $classTypes, true);
    }
}
