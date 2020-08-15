<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\PropertyRanker;

/**
 * @see \Rector\Order\Tests\Rector\Class_\OrderPropertyByComplexityRector\OrderPropertyByComplexityRectorTest
 */
final class OrderPropertyByComplexityRector extends AbstractRector
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->stmts = $this->sortPublicPropertiesByComplexity($node->stmts);
        $node->stmts = $this->sortProtectedPropertiesByComplexity($node->stmts);
        $node->stmts = $this->sortPrivatePropertiesByComplexity($node->stmts);
        return $node;
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function sortPublicPropertiesByComplexity(array $stmts)
    {
        usort(
            $stmts,
            function (Stmt $firstStmt, Stmt $secondStmt): int {
                if (! $firstStmt instanceof Property || ! $secondStmt instanceof Property) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }

                if (! $firstStmt->isPublic() || ! $secondStmt->isPublic()) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }

                return [
                    $this->propertyRanker->rank($firstStmt),
                    $firstStmt->getLine(),
                ] <=> [$this->propertyRanker->rank($secondStmt), $secondStmt->getLine()];
            }
        );
        return $stmts;
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function sortProtectedPropertiesByComplexity(array $stmts)
    {
        usort(
            $stmts,
            function (Stmt $firstStmt, Stmt $secondStmt): int {
                if (! $firstStmt instanceof Property || ! $secondStmt instanceof Property) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }

                if (! $firstStmt->isProtected() || ! $secondStmt->isProtected()) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }

                return [
                    $this->propertyRanker->rank($firstStmt),
                    $firstStmt->getLine(),
                ] <=> [$this->propertyRanker->rank($secondStmt), $secondStmt->getLine()];
            }
        );
        return $stmts;
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function sortPrivatePropertiesByComplexity(array $stmts)
    {
        usort(
            $stmts,
            function (Stmt $firstStmt, Stmt $secondStmt): int {
                if (! $firstStmt instanceof Property || ! $secondStmt instanceof Property) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }

                if (! $firstStmt->isPrivate() || ! $secondStmt->isPrivate()) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }

                return [
                    $this->propertyRanker->rank($firstStmt),
                    $firstStmt->getLine(),
                ] <=> [$this->propertyRanker->rank($secondStmt), $secondStmt->getLine()];
            }
        );
        return $stmts;
    }
}
