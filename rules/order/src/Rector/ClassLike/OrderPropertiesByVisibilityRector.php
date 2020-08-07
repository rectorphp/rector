<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtOrder;

/**
 * @see \Rector\Order\Tests\Rector\ClassLike\OrderPropertiesByVisibilityRector\OrderPropertiesByVisibilityRectorTest
 */
final class OrderPropertiesByVisibilityRector extends AbstractRector
{
    /**
     * @var string
     */
    private const VISIBILITY = 'visibility';

    /**
     * @var string
     */
    private const POSITION = 'position';

    /**
     * @var StmtOrder
     */
    private $stmtOrder;

    public function __construct(StmtOrder $stmtOrder)
    {
        $this->stmtOrder = $stmtOrder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Orders properties by visibility', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    protected $protectedProperty;
    private $privateProperty;
    public $publicProperty;
}
PHP

                ,
                <<<'PHP'
final class SomeClass
{
    public $publicProperty;
    protected $protectedProperty;
    private $privateProperty;
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassLike::class];
    }

    /**
     * @param ClassLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Node\Stmt\Interface_) {
            return null;
        }

        $properties = [];
        $propertiesByName = [];
        foreach ($node->stmts as $position => $propertyStmt) {
            if (! $propertyStmt instanceof Node\Stmt\Property) {
                continue;
            }

            /** @var string $propertyName */
            $propertyName = $this->getName($propertyStmt);
            $propertiesByName[$position] = $propertyName;

            $properties[$propertyName]['name'] = $propertyName;
            $properties[$propertyName][self::VISIBILITY] = $this->stmtOrder->getOrderByVisibility($propertyStmt);
            $properties[$propertyName]['static'] = $propertyStmt->isStatic();
            $properties[$propertyName][self::POSITION] = $position;
        }

        $sortedProperties = $this->getPropertiesSortedByVisibility($properties);
        $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($sortedProperties, $propertiesByName);

        return $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
    }

    private function getPropertiesSortedByVisibility(array $properties): array
    {
        uasort(
            $properties,
            function (array $firstArray, array $secondArray): int {
                return [
                    $firstArray[self::VISIBILITY],
                    $firstArray['static'],
                    $firstArray[self::POSITION],
                ] <=> [$secondArray[self::VISIBILITY], $secondArray['static'], $secondArray[self::POSITION]];
            }
        );

        return array_keys($properties);
    }
}
