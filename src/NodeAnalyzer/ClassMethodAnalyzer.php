<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Read-only utils for ClassMethod Node:
 * public "function" SomeMethod()
 */
final class ClassMethodAnalyzer
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
     * @param string[] $methods
     */
    public function isTypeAndMethods(Node $node, string $type, array $methods): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var ClassMethod $node */
        $methodName = (string) $node->name;

        return in_array($methodName, $methods, true);
    }

    private function isType(Node $node, string $type): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        $nodeTypes = $this->nodeTypeResolver->resolve($classNode);

        return in_array($type, $nodeTypes, true);
    }
}
