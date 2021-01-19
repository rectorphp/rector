<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
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
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        NodeRepository $nodeRepository,
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
                                NodeNameResolver $nodeNameResolver
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
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

        $nodesToSearch = $this->nodeRepository->findUsedTraitsInClass($classLike);
        $nodesToSearch[] = $classLike;

        $singleProperty = $property->props[0];

        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->find($nodesToSearch, function (Node $node) use (
            $singleProperty,
            $nodesToSearch
        ): bool {
            // property + static fetch
            if (! $node instanceof PropertyFetch && ! $node instanceof StaticPropertyFetch) {
                return false;
            }

            // itself
//            if ($this->betterStandardPrinter->areNodesEqual($node, $singleProperty)) {
//                return false;
//            }

            // is it the name match?
            if (! $this->nodeNameResolver->areNamesEqual($node, $singleProperty)) {
                return false;
            }

            return in_array($node->getAttribute(AttributeKey::CLASS_NODE), $nodesToSearch, true);
        });

        return $propertyFetches;
    }
}
