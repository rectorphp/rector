<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class StaticMethodCallAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * Checks "SpecificType::specificMethod()"
     */
    public function isTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var StaticCall $staticCallNode */
        $staticCallNode = $node;

        return (string) $staticCallNode->name === $method;
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

        /** @var StaticCall $staticCallNode */
        $staticCallNode = $node;

        $currentMethodName = (string) $staticCallNode->name;

        return in_array($currentMethodName, $methodNames, true);
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

        if (! $node->name instanceof Identifier) {
            return null;
        }

        if (! $node->class instanceof Name) {
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
