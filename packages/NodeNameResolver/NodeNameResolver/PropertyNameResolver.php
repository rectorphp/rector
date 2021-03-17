<?php

declare(strict_types=1);

namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;

final class PropertyNameResolver implements NodeNameResolverInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @required
     */
    public function autowirePropertyNameResolver(NodeNameResolver $nodeNameResolver): void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function getNode(): string
    {
        return Property::class;
    }

    /**
     * @param Property $node
     */
    public function resolve(Node $node): ?string
    {
        if ($node->props === []) {
            return null;
        }

        $onlyProperty = $node->props[0];

        return $this->nodeNameResolver->getName($onlyProperty);
    }
}
