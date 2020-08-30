<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtOrder;
use Rector\Order\StmtVisibilitySorter;

/**
 * @see \Rector\Order\Tests\Rector\ClassLike\OrderPropertiesByVisibilityRector\OrderPropertiesByVisibilityRectorTest
 */
final class OrderPropertiesByVisibilityRector extends AbstractRector
{
    /**
     * @var StmtOrder
     */
    private $stmtOrder;

    /**
     * @var StmtVisibilitySorter
     */
    private $stmtVisibilitySorter;

    public function __construct(StmtOrder $stmtOrder, StmtVisibilitySorter $stmtVisibilitySorter)
    {
        $this->stmtOrder = $stmtOrder;
        $this->stmtVisibilitySorter = $stmtVisibilitySorter;
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
        if ($node instanceof Interface_) {
            return null;
        }

        $currentPropertiesOrder = $this->stmtOrder->getStmtsOfTypeOrder($node, Property::class);
        $propertiesInDesiredOrder = $this->getPropertiesInDesiredPosition($node);

        $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($propertiesInDesiredOrder, $currentPropertiesOrder);

        // nothing to re-order
        if (array_keys($oldToNewKeys) === array_values($oldToNewKeys)) {
            return null;
        }

        return $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
    }

    /**
     * @return string[]
     */
    private function getPropertiesInDesiredPosition(ClassLike $classLike): array
    {
        $properties = $this->stmtVisibilitySorter->sortProperties($classLike);

        return array_keys($properties);
    }
}
