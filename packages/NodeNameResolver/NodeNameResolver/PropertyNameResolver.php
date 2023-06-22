<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<Property>
 */
final class PropertyNameResolver implements NodeNameResolverInterface
{
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
        return $onlyProperty->name->toString();
    }
}
