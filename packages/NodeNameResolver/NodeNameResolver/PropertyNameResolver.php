<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix202306\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements NodeNameResolverInterface<Property>
 */
final class PropertyNameResolver implements NodeNameResolverInterface
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @required
     */
    public function autowire(NodeNameResolver $nodeNameResolver) : void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function getNode() : string
    {
        return Property::class;
    }
    /**
     * @param Property $node
     */
    public function resolve(Node $node, ?Scope $scope) : ?string
    {
        if ($node->props === []) {
            return null;
        }
        $onlyProperty = $node->props[0];
        return $this->nodeNameResolver->getName($onlyProperty);
    }
}
