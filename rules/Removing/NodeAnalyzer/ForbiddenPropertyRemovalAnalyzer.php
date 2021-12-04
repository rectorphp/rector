<?php

declare(strict_types=1);

namespace Rector\Removing\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ForbiddenPropertyRemovalAnalyzer
{
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly NodeComparator $nodeComparator,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly NodeTypeResolver $nodeTypeResolver,
        private readonly PropertyFetchAnalyzer $propertyFetchAnalyzer
    ) {
    }

    public function isForbiddenInNewCurrentClassNameSelfClone(string $propertyName, ?ClassLike $classLike): bool
    {
        if (! $classLike instanceof ClassLike) {
            return false;
        }

        $methods = $classLike->getMethods();
        foreach ($methods as $method) {
            $isInNewCurrentClassNameSelfClone = (bool) $this->betterNodeFinder->findFirst(
                (array) $method->getStmts(),
                function (Node $subNode) use ($classLike, $propertyName): bool {
                    if ($subNode instanceof New_) {
                        return $this->isPropertyNameUsedAfterNewOrClone($subNode, $classLike, $propertyName);
                    }

                    if ($subNode instanceof Clone_) {
                        return $this->isPropertyNameUsedAfterNewOrClone($subNode, $classLike, $propertyName);
                    }

                    return false;
                }
            );

            if ($isInNewCurrentClassNameSelfClone) {
                return true;
            }
        }

        return false;
    }

    private function isPropertyNameUsedAfterNewOrClone(
        New_|Clone_ $expr,
        ClassLike $classLike,
        string $propertyName
    ): bool {
        $parentAssign = $this->betterNodeFinder->findParentType($expr, Assign::class);
        if (! $parentAssign instanceof Assign) {
            return false;
        }

        $className = (string) $this->nodeNameResolver->getName($classLike);
        $type = $expr instanceof New_
            ? $this->nodeTypeResolver->getType($expr->class)
            : $this->nodeTypeResolver->getType($expr->expr);

        if ($expr instanceof Clone_ && $type instanceof ThisType) {
            $type = $type->getStaticObjectType();
        }

        if ($type instanceof ObjectType) {
            return $this->isFoundAfterCloneOrNew($type, $expr, $parentAssign, $className, $propertyName);
        }

        return false;
    }

    private function isFoundAfterCloneOrNew(
        ObjectType $objectType,
        Clone_|New_ $expr,
        Assign $parentAssign,
        string $className,
        string $propertyName
    ): bool {
        if ($objectType->getClassName() !== $className) {
            return false;
        }

        return (bool) $this->betterNodeFinder->findFirstNext($expr, function (Node $subNode) use (
            $parentAssign,
            $propertyName
        ): bool {
            if (! $this->propertyFetchAnalyzer->isPropertyFetch($subNode)) {
                return false;
            }

            /** @var PropertyFetch|StaticPropertyFetch $subNode */
            $propertyFetchName = (string) $this->nodeNameResolver->getName($subNode);
            if ($subNode instanceof PropertyFetch) {
                if (! $this->nodeComparator->areNodesEqual($subNode->var, $parentAssign->var)) {
                    return false;
                }

                return $propertyFetchName === $propertyName;
            }

            if (! $this->nodeComparator->areNodesEqual($subNode->class, $parentAssign->var)) {
                return false;
            }

            return $propertyFetchName === $propertyName;
        });
    }
}
