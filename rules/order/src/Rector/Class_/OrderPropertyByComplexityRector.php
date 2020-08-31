<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\PropertyRanker;
use Rector\Order\Rector\AbstractConstantPropertyMethodOrderRector;
use Rector\Order\ValueObject\PropertyNameRankAndPosition;

/**
 * @see \Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\OrderPropertyByComplexityRectorTest
 */
final class OrderPropertyByComplexityRector extends AbstractConstantPropertyMethodOrderRector
{
    /**
     * @var PropertyRanker
     */
    private $propertyRanker;

    public function __construct(PropertyRanker $propertyRanker)
    {
        $this->propertyRanker = $propertyRanker;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Order properties by complexity, from the simplest like scalars to the most complex, like union or collections',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $service;

    /**
     * @var int
     */
    private $price;
}
PHP
,
                    <<<'PHP'
class SomeClass implements FoodRecipeInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $price;

    /**
     * @var Type
     */
    private $service;
}
PHP
            ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Trait_::class];
    }

    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $propertyByVisibilityByPosition = $this->resolvePropertyByVisibilityByPosition($node);

        foreach ($propertyByVisibilityByPosition as $propertyByPosition) {
            $propertyPositionByName = [];

            $propertyNamesRanksAndPositions = [];

            foreach ($propertyByPosition as $position => $property) {
                /** @var string $propertyName */
                $propertyName = $this->getName($property);

                $propertyPositionByName[$position] = $propertyName;

                $rank = $this->propertyRanker->rank($property);
                $propertyNamesRanksAndPositions[] = new PropertyNameRankAndPosition(
                    $propertyName,
                    $rank,
                    $position
                );
            }

            $sortedPropertyByRank = $this->getSortedPropertiesByRankAndPosition($propertyNamesRanksAndPositions);
            $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($sortedPropertyByRank, $propertyPositionByName);

            // nothing to re-order
            if (! $this->hasOrderChanged($oldToNewKeys)) {
                continue;
            }

            $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
        }

        return $node;
    }

    /**
     * @param Class_|Trait_ $classLike
     * @return array<string, Property[]>
     */
    private function resolvePropertyByVisibilityByPosition(ClassLike $classLike): array
    {
        $propertyByVisibilityByPosition = [];
        foreach ((array) $classLike->stmts as $position => $classStmt) {
            if (! $classStmt instanceof Property) {
                continue;
            }

            $visibility = $this->getVisibilityAsString($classStmt);
            $propertyByVisibilityByPosition[$visibility][$position] = $classStmt;
        }

        return $propertyByVisibilityByPosition;
    }

    /**
     * @param PropertyNameRankAndPosition[] $propertyNamesRanksAndPositions
     * @return string[]
     */
    private function getSortedPropertiesByRankAndPosition(array $propertyNamesRanksAndPositions): array
    {
        uasort(
            $propertyNamesRanksAndPositions,
            function (PropertyNameRankAndPosition $firstArray, PropertyNameRankAndPosition $secondArray): int {
                return [$firstArray->getRank(), $firstArray->getPosition()]
                    <=>
                    [$secondArray->getRank(), $secondArray->getPosition()];
            }
        );

        $propertyNames = [];
        foreach ($propertyNamesRanksAndPositions as $propertyNameRankAndPosition) {
            $propertyNames[] = $propertyNameRankAndPosition->getName();
        }

        return $propertyNames;
    }

    private function getVisibilityAsString(Property $property): string
    {
        if ($property->isPrivate()) {
            return 'private';
        }

        if ($property->isProtected()) {
            return 'protected';
        }

        return 'public';
    }
}
