<?php

declare(strict_types=1);

namespace Rector\FamilyTree\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\StaticRelationsHelper;
use Rector\NodeCollector\NodeFinder\ClassLikeParsedNodesFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyUsageAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ClassLikeParsedNodesFinder
     */
    private $classLikeParsedNodesFinder;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ClassLikeParsedNodesFinder $classLikeParsedNodesFinder,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classLikeParsedNodesFinder = $classLikeParsedNodesFinder;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function isPropertyFetchedInChildClass(Property $property): bool
    {
        $className = $property->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        $class = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($class instanceof Class_ && $class->isFinal()) {
            return false;
        }

        $propertyName = $this->nodeNameResolver->getName($property);
        if ($propertyName === null) {
            return false;
        }

        $childrenClassNames = StaticRelationsHelper::getChildrenOfClass($className);
        foreach ($childrenClassNames as $childClassName) {
            $childClass = $this->classLikeParsedNodesFinder->findClass($childClassName);
            if ($childClass === null) {
                continue;
            }

            $isPropertyFetched = (bool) $this->betterNodeFinder->findFirst(
                (array) $childClass->stmts,
                function (Node $node) use ($propertyName) {
                    return $this->isLocalPropertyFetchNamed($node, $propertyName);
                }
            );

            if ($isPropertyFetched) {
                return true;
            }
        }

        return false;
    }

    private function isLocalPropertyFetchNamed(Node $node, string $name): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($node->var, 'this')) {
            return false;
        }

        return $this->nodeNameResolver->isName($node->name, $name);
    }
}
