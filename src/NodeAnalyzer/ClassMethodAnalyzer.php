<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\MetadataAttribute;
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

        /** @var Identifier $methodName */
        $methodName = $node->name;

        return in_array($methodName->toString(), $methods, true);
    }

    private function isType(Node $node, string $type): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        $classNode = $node->getAttribute(MetadataAttribute::CLASS_NODE);
        $nodeTypes = $this->nodeTypeResolver->resolve($classNode);

        return in_array($type, $nodeTypes, true);
    }
}
