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
        foreach ($perNodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->perNodeTypeResolvers[$nodeClass] = $perNodeTypeResolver;
        }
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        $nodeClass = get_class($node);

        if (! isset($this->perNodeTypeResolvers[$nodeClass])) {
            return [];
        }

        // resolve just once
        if ($node->getAttribute(Attribute::TYPES)) {
            return $node->getAttribute(Attribute::TYPES);
        }

        return $this->perNodeTypeResolvers[$nodeClass]->resolve($node);
    }
}
