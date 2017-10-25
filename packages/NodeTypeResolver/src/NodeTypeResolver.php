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
        $this->perNodeTypeResolvers[$perNodeTypeResolver->getNodeClass()] = $perNodeTypeResolver;
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        foreach ($this->perNodeTypeResolvers as $class => $perNodeTypeResolver) {
            if (! $node instanceof $class) {
                continue;
            }

            // resolve just once
            if ($node->getAttribute(Attribute::TYPES)) {
                return $node->getAttribute(Attribute::TYPES);
            }

            return $perNodeTypeResolver->resolve($node);
        }

        return null;
    }
}
