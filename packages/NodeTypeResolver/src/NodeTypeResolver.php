<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use Rector\Exception\NotImplementedException;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

    public function addPerNodeTypeResolver(PerNodeTypeResolverInterface $perNodeTypeResolver): void
    {
        $this->perNodeTypeResolvers[] = $perNodeTypeResolver;
    }

    public function resolve(Node $node): ?string
    {
        foreach ($this->perNodeTypeResolvers as $perNodeTypeResolver) {
            if (! is_a($node, $perNodeTypeResolver->getNodeClass(), true)) {
                continue;
            }

            return $perNodeTypeResolver->resolve($node);
        }

        return null;

//        throw new NotImplementedException(sprintf(
//            '%s() was unable to resolve "%s" Node type. Create new class that implements "%s".',
//            __METHOD__,
//            get_class($node),
//            PerNodeTypeResolverInterface::class
//        ));
    }
}
