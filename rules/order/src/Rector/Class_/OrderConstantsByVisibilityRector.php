<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtOrder;
use Rector\Order\StmtVisibilitySorter;

/**
 * @see \Rector\Order\Tests\Rector\Class_\OrderConstantsByVisibilityRector\OrderConstantsByVisibilityRectorTest
 */
final class OrderConstantsByVisibilityRector extends AbstractRector
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
        return new RectorDefinition('Orders constants by visibility', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    private const PRIVATE_CONST = 'private';
    protected const PROTECTED_CONST = 'protected';
    public const PUBLIC_CONST = 'public';
}
PHP

                ,
                <<<'PHP'
final class SomeClass
{
    public const PUBLIC_CONST = 'public';
    protected const PROTECTED_CONST = 'protected';
    private const PRIVATE_CONST = 'private';
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $currentPropertiesOrder = $this->stmtOrder->getStmtsOfTypeOrder($node, ClassConst::class);
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
    private function getPropertiesInDesiredPosition(Class_ $class): array
    {
        $constants = $this->stmtVisibilitySorter->sortConstants($class);

        return array_keys($constants);
    }
}
