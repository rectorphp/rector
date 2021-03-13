<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Rector\AbstractRector;
use Rector\Order\Order\OrderChangeAnalyzer;
use Rector\Order\StmtOrder;
use Rector\Order\StmtVisibilitySorter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Order\Rector\Class_\OrderConstantsByVisibilityRector\OrderConstantsByVisibilityRectorTest
 */
final class OrderConstantsByVisibilityRector extends AbstractRector
{
    /**
     * @var OrderChangeAnalyzer
     */
    private $orderChangeAnalyzer;

    /**
     * @var StmtOrder
     */
    private $stmtOrder;

    /**
     * @var StmtVisibilitySorter
     */
    private $stmtVisibilitySorter;

    public function __construct(
        OrderChangeAnalyzer $orderChangeAnalyzer,
        StmtOrder $stmtOrder,
        StmtVisibilitySorter $stmtVisibilitySorter
    ) {
        $this->orderChangeAnalyzer = $orderChangeAnalyzer;
        $this->stmtOrder = $stmtOrder;
        $this->stmtVisibilitySorter = $stmtVisibilitySorter;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Orders constants by visibility', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private const PRIVATE_CONST = 'private';
    protected const PROTECTED_CONST = 'protected';
    public const PUBLIC_CONST = 'public';
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public const PUBLIC_CONST = 'public';
    protected const PROTECTED_CONST = 'protected';
    private const PRIVATE_CONST = 'private';
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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
        $currentClassConstsOrder = $this->stmtOrder->getStmtsOfTypeOrder($node, ClassConst::class);
        $classConstsInDesiredOrder = $this->stmtVisibilitySorter->sortConstants($node);

        $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($classConstsInDesiredOrder, $currentClassConstsOrder);

        if (! $this->orderChangeAnalyzer->hasOrderChanged($oldToNewKeys)) {
            return null;
        }

        return $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
    }
}
