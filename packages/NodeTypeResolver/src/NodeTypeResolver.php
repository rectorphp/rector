<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

    public function addPerNodeTypeResolver(PerNodeTypeResolverInterface $perNodeTypeResolver): void
    {
        foreach ($perNodeTypeResolver->getNodeTypes() as $nodeType) {
            $this->perNodeTypeResolvers[$nodeType] = $perNodeTypeResolver;
        }
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        if (! isset($this->perNodeTypeResolvers[$node->getType()])) {
            return [];
        }

        // resolve just once
        if ($node->getAttribute(Attribute::TYPES)) {
            return $node->getAttribute(Attribute::TYPES);
        }

        return $this->perNodeTypeResolvers[$node->getType()]->resolve($node);
    }
}
