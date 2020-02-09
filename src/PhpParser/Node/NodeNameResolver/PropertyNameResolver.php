<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\NameResolver\NodeNameResolverInterface;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;

final class PropertyNameResolver implements NodeNameResolverInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @required
     */
    public function autowireClassNameResolver(NodeNameResolver $nodeNameResolver): void
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
        if (count($node->props) === 0) {
            return null;
        }

        $onlyProperty = $node->props[0];

        return $this->nodeNameResolver->getName($onlyProperty);
    }
}
