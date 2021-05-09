<?php

declare (strict_types=1);
namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Rector\AbstractRector;
use Rector\Order\Order\OrderChangeAnalyzer;
use Rector\Order\StmtOrder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector\OrderClassConstantsByIntegerValueRectorTest
 */
final class OrderClassConstantsByIntegerValueRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var OrderChangeAnalyzer
     */
    private $orderChangeAnalyzer;
    /**
     * @var StmtOrder
     */
    private $stmtOrder;
    public function __construct(\Rector\Order\Order\OrderChangeAnalyzer $orderChangeAnalyzer, \Rector\Order\StmtOrder $stmtOrder)
    {
        $this->orderChangeAnalyzer = $orderChangeAnalyzer;
        $this->stmtOrder = $stmtOrder;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Order class constant order by their integer value', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    const MODE_ON = 0;

    const MODE_OFF = 2;

    const MODE_MAYBE = 1;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    const MODE_ON = 0;

    const MODE_MAYBE = 1;

    const MODE_OFF = 2;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $numericClassConstsByKey = $this->resolveClassConstByPosition($node);
        if ($numericClassConstsByKey === []) {
            return null;
        }
        $classConstConstsByValue = $this->resolveClassConstConstByUniqueValue($numericClassConstsByKey);
        $sortedClassConstConstsByValue = $classConstConstsByValue;
        \asort($sortedClassConstConstsByValue);
        $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($sortedClassConstConstsByValue, $classConstConstsByValue);
        if (!$this->orderChangeAnalyzer->hasOrderChanged($oldToNewKeys)) {
            return null;
        }
        $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
        return $node;
    }
    /**
     * @return ClassConst[]
     */
    private function resolveClassConstByPosition(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $classConstConstsByValue = [];
        foreach ($class->stmts as $key => $classStmt) {
            if (!$classStmt instanceof \PhpParser\Node\Stmt\ClassConst) {
                continue;
            }
            if (\count($classStmt->consts) !== 1) {
                continue;
            }
            $classConstConst = $classStmt->consts[0];
            if (!$classConstConst->value instanceof \PhpParser\Node\Scalar\LNumber) {
                continue;
            }
            $classConstConstsByValue[$key] = $classStmt;
        }
        return $classConstConstsByValue;
    }
    /**
     * @param array<int, ClassConst> $numericClassConstsByKey
     * @return array<int, string>
     */
    private function resolveClassConstConstByUniqueValue(array $numericClassConstsByKey) : array
    {
        $classConstConstsByValue = [];
        foreach ($numericClassConstsByKey as $position => $numericClassConst) {
            $constantValue = $this->valueResolver->getValue($numericClassConst->consts[0]->value);
            $classConstConstsByValue[$position] = $constantValue;
        }
        $arrayCountValue = \array_count_values($classConstConstsByValue);
        // work only with unique constants
        foreach ($classConstConstsByValue as $position => $constantValue) {
            if ($arrayCountValue[$constantValue] > 1) {
                unset($classConstConstsByValue[$position]);
            }
        }
        return $classConstConstsByValue;
    }
}
