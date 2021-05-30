<?php

declare (strict_types=1);
namespace Rector\Order;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
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
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_ $classLike
     */
    public function sortProperties($classLike) : array
    {
        $propertyRankeables = [];
        foreach ($classLike->stmts as $position => $propertyStmt) {
            if (!$propertyStmt instanceof \PhpParser\Node\Stmt\Property) {
                continue;
            }
            /** @var string $propertyName */
            $propertyName = $this->nodeNameResolver->getName($propertyStmt);
            $propertyRankeables[] = new \Rector\Order\ValueObject\PropertyRankeable($propertyName, $this->getVisibilityLevelOrder($propertyStmt), $propertyStmt, $position);
        }
        return $this->sortByRanksAndGetNames($propertyRankeables);
    }
    /**
     * @return string[]
     */
    public function sortMethods(\PhpParser\Node\Stmt\ClassLike $classLike) : array
    {
        $classMethodsRankeables = [];
        foreach ($classLike->stmts as $position => $classStmt) {
            if (!$classStmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            /** @var string $classMethodName */
            $classMethodName = $this->nodeNameResolver->getName($classStmt);
            $classMethodsRankeables[] = new \Rector\Order\ValueObject\ClassMethodRankeable($classMethodName, $this->getVisibilityLevelOrder($classStmt), $position, $classStmt);
        }
        return $this->sortByRanksAndGetNames($classMethodsRankeables);
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_ $classLike
     */
    public function sortConstants($classLike) : array
    {
        $classConstsRankeables = [];
        foreach ($classLike->stmts as $position => $constantStmt) {
            if (!$constantStmt instanceof \PhpParser\Node\Stmt\ClassConst) {
                continue;
            }
            /** @var string $constantName */
            $constantName = $this->nodeNameResolver->getName($constantStmt);
            $classConstsRankeables[] = new \Rector\Order\ValueObject\ClassConstRankeable($constantName, $this->getVisibilityLevelOrder($constantStmt), $position);
        }
        return $this->sortByRanksAndGetNames($classConstsRankeables);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassConst $stmt
     */
    private function getVisibilityLevelOrder($stmt) : int
    {
        if ($stmt->isPrivate()) {
            return 2;
        }
        if ($stmt->isProtected()) {
            return 1;
        }
        return 0;
    }
    /**
     * @param RankeableInterface[] $rankeables
     * @return string[]
     */
    private function sortByRanksAndGetNames(array $rankeables) : array
    {
        \uasort($rankeables, function (\Rector\Order\Contract\RankeableInterface $firstRankeable, \Rector\Order\Contract\RankeableInterface $secondRankeable) : int {
            return $firstRankeable->getRanks() <=> $secondRankeable->getRanks();
        });
        $names = [];
        foreach ($rankeables as $rankeable) {
            $names[] = $rankeable->getName();
        }
        return $names;
    }
}
