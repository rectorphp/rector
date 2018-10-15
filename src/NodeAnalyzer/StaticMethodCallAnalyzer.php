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

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
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
}
