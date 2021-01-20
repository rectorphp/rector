<?php

declare(strict_types=1);

namespace Rector\FamilyTree\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyUsageAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        FamilyRelationsAnalyzer $familyRelationsAnalyzer,
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->nodeRepository = $nodeRepository;
    }

    public function isPropertyFetchedInChildClass(Property $property): bool
    {
        $className = $property->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike instanceof Class_ && $classLike->isFinal()) {
            return false;
        }

        $propertyName = $this->nodeNameResolver->getName($property);

        $childrenClassNames = $this->familyRelationsAnalyzer->getChildrenOfClass($className);
        foreach ($childrenClassNames as $childClassName) {
            $childClass = $this->nodeRepository->findClass($childClassName);
            if (! $childClass instanceof Class_) {
                continue;
            }

            $isPropertyFetched = (bool) $this->betterNodeFinder->findFirst(
                $childClass->stmts,
                function (Node $node) use ($propertyName): bool {
                    return $this->nodeNameResolver->isLocalPropertyFetchNamed($node, $propertyName);
                }
            );

            if ($isPropertyFetched) {
                return true;
            }
        }

        return false;
    }
}
