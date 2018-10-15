<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait TypeAnalyzerTrait
{
    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @required
     */
    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isType(Node $node, string $type): bool
    {
        $nodeTypes = $this->getTypes($node);
        return in_array($type, $nodeTypes, true);
    }

    /**
     * @param string[] $types
     */
    public function isTypes(Node $node, array $types): bool
    {
        $nodeTypes = $this->getTypes($node);
        return (bool) array_intersect($types, $nodeTypes);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    public function matchTypes(Node $node, array $types): array
    {
        return $this->isTypes($node, $types) ? $this->getTypes($node) : [];
    }

    /**
     * @return string[]
     */
    public function getTypes(Node $node): array
    {
        if ($node instanceof ClassMethod) {
            return $this->nodeTypeResolver->resolve($node->getAttribute(Attribute::CLASS_NODE));
        }

        if ($node instanceof MethodCall || $node instanceof PropertyFetch) {
            return $this->nodeTypeResolver->resolve($node->var);
        }

        if ($node instanceof StaticCall) {
            return $this->nodeTypeResolver->resolve($node->class);
        }

        if ($node instanceof ClassConstFetch) {
            return $this->nodeTypeResolver->resolve($node->class);
        }

        return $this->nodeTypeResolver->resolve($node);
    }
}
