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
        foreach ($perNodeCallerTypeResolver->getNodeClasses() as $nodeClass) {
            $this->perNodeCallerTypeResolvers[$nodeClass] = $perNodeCallerTypeResolver;
        }
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        $nodeClass = get_class($node);

        if (! isset($this->perNodeCallerTypeResolvers[$nodeClass])) {
            return [];
        }

        return $this->perNodeCallerTypeResolvers[$nodeClass]->resolve($node);
    }
}
