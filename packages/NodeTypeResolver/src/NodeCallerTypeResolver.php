<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use Rector\NodeTypeResolver\Contract\PerNodeCallerTypeResolver\PerNodeCallerTypeResolverInterface;

/**
 * This will tell the type of Node, which is calling this method
 *
 * E.g.:
 * - {$this}->callMe()
 * - $this->{getThis()}->callMe()
 * - {parent}::callMe()
 */
final class NodeCallerTypeResolver
{
    /**
     * @var PerNodeCallerTypeResolverInterface[]
     */
    private $perNodeCallerTypeResolvers = [];

    public function addPerNodeCallerTypeResolver(PerNodeCallerTypeResolverInterface $perNodeCallerTypeResolver): void
    {
        foreach ($perNodeCallerTypeResolver->getNodeTypes() as $nodeType) {
            $this->perNodeCallerTypeResolvers[$nodeType] = $perNodeCallerTypeResolver;
        }
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        if (! isset($this->perNodeCallerTypeResolvers[$node->getType()])) {
            return [];
        }

        return $this->perNodeCallerTypeResolvers[$node->getType()]->resolve($node);
    }
}
