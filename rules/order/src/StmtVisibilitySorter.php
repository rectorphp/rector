<?php

declare(strict_types=1);

namespace Rector\Order;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Order\Contract\RankeableInterface;
use Rector\Order\ValueObject\ClassConstRankeable;
use Rector\Order\ValueObject\ClassMethodRankeable;
use Rector\Order\ValueObject\PropertyRankeable;

final class StmtVisibilitySorter
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param Class_|Trait_ $classLike
     * @return RankeableInterface[]
     */
    public function sortProperties(ClassLike $classLike): array
    {
        $propertyRankeables = [];

        foreach ($classLike->stmts as $position => $propertyStmt) {
            if (! $propertyStmt instanceof Property) {
                continue;
            }

            /** @var string $propertyName */
            $propertyName = $this->nodeNameResolver->getName($propertyStmt);

            $propertyRankeables[] = new PropertyRankeable(
                $propertyName,
                $this->getVisibilityLevelOrder($propertyStmt),
                $propertyStmt,
                $position
            );
        }

        uasort(
            $propertyRankeables,
            function (RankeableInterface $firstRankeable, RankeableInterface $secondRankeable): int {
                return $firstRankeable->getRanks() <=> $secondRankeable->getRanks();
            }
        );

        return $propertyRankeables;
    }

    /**
     * @return RankeableInterface[]
     */
    public function sortMethods(ClassLike $classLike): array
    {
        $classMethodsOrderMetadata = [];
        foreach ($classLike->stmts as $position => $classStmt) {
            if (! $classStmt instanceof ClassMethod) {
                continue;
            }

            /** @var string $classMethodName */
            $classMethodName = $this->nodeNameResolver->getName($classStmt);

            $classMethodsOrderMetadata[] = new ClassMethodRankeable(
                $classMethodName,
                $this->getVisibilityLevelOrder($classStmt),
                $position,
                $classStmt
            );
        }

        uasort(
            $classMethodsOrderMetadata,
            function (RankeableInterface $firstRankeable, RankeableInterface $secondRankeable): int {
                return $firstRankeable->getRanks() <=> $secondRankeable->getRanks();
            }
        );

        return $classMethodsOrderMetadata;
    }

    /**
     * @return array<string,array<string, mixed>>
     */
    public function sortConstants(ClassLike $classLike): array
    {
        $classConstsRankeables = [];
        foreach ($classLike->stmts as $position => $constantStmt) {
            if (! $constantStmt instanceof ClassConst) {
                continue;
            }

            /** @var string $constantName */
            $constantName = $this->nodeNameResolver->getName($constantStmt);

            $classConstsRankeables[] = new ClassConstRankeable(
                $constantName,
                $this->getVisibilityLevelOrder($constantStmt),
                $position
            );
        }

        uasort(
            $classConstsRankeables,
            function (RankeableInterface $firstRankeable, RankeableInterface $secondRankeable): int {
                return $firstRankeable->getRanks() <=> $secondRankeable->getRanks();
            }
        );

        return $classConstsRankeables;
    }

    /**
     * @param ClassMethod|Property|ClassConst $stmt
     */
    private function getVisibilityLevelOrder(Stmt $stmt): int
    {
        if ($stmt->isPrivate()) {
            return 2;
        }

        if ($stmt->isProtected()) {
            return 1;
        }

        return 0;
    }
}
