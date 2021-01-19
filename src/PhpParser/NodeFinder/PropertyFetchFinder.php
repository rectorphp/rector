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
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        NodeRepository $nodeRepository,
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
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
}
