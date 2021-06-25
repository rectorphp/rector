<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyFetchFinder
{
    public function __construct(
        private NodeRepository $nodeRepository,
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    public function findPrivatePropertyFetches(Property $property): array
    {
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return [];
        }

        $classLikesToSearch = $this->nodeRepository->findUsedTraitsInClass($classLike);
        $classLikesToSearch[] = $classLike;

        $singleProperty = $property->props[0];

        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->find($classLikesToSearch, function (Node $node) use (
            $singleProperty,
            $classLikesToSearch
        ): bool {
            // property + static fetch
            if (! $node instanceof PropertyFetch && ! $node instanceof StaticPropertyFetch) {
                return false;
            }

            // is it the name match?
            if (! $this->nodeNameResolver->areNamesEqual($node, $singleProperty)) {
                return false;
            }

            $currentClassLike = $node->getAttribute(AttributeKey::CLASS_NODE);
            return in_array($currentClassLike, $classLikesToSearch, true);
        });

        return $propertyFetches;
    }

    /**
     * @return PropertyFetch[]
     */
    public function findLocalPropertyFetchesByName(Class_ $class, string $paramName): array
    {
        /** @var PropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->findInstanceOf($class, PropertyFetch::class);

        $foundPropertyFetches = [];

        foreach ($propertyFetches as $propertyFetch) {
            if (! $this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                continue;
            }

            if (! $this->nodeNameResolver->isName($propertyFetch->name, $paramName)) {
                continue;
            }

            $foundPropertyFetches[] = $propertyFetch;
        }

        return $foundPropertyFetches;
    }
}
