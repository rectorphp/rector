<?php

declare (strict_types=1);
namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Order\Order\OrderChangeAnalyzer;
use Rector\Order\StmtOrder;
use Rector\Order\StmtVisibilitySorter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Order\Rector\Class_\OrderMethodsByVisibilityRector\OrderMethodsByVisibilityRectorTest
 */
final class OrderMethodsByVisibilityRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const PREFERRED_ORDER = [\Rector\Core\ValueObject\MethodName::CONSTRUCT, \Rector\Core\ValueObject\MethodName::DESCTRUCT, '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep', '__wakeup', '__serialize', '__unserialize', '__toString', '__invoke', \Rector\Core\ValueObject\MethodName::SET_STATE, \Rector\Core\ValueObject\MethodName::CLONE, 'setUpBeforeClass', 'tearDownAfterClass', \Rector\Core\ValueObject\MethodName::SET_UP, \Rector\Core\ValueObject\MethodName::TEAR_DOWN];
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
    public function __construct(\Rector\Order\Order\OrderChangeAnalyzer $orderChangeAnalyzer, \Rector\Order\StmtOrder $stmtOrder, \Rector\Order\StmtVisibilitySorter $stmtVisibilitySorter)
    {
        $this->orderChangeAnalyzer = $orderChangeAnalyzer;
        $this->stmtOrder = $stmtOrder;
        $this->stmtVisibilitySorter = $stmtVisibilitySorter;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Orders method by visibility', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    protected function protectedFunctionName();
    private function privateFunctionName();
    public function publicFunctionName();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function publicFunctionName();
    protected function protectedFunctionName();
    private function privateFunctionName();
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Trait_::class];
    }
    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $currentMethodsOrder = $this->stmtOrder->getStmtsOfTypeOrder($node, \PhpParser\Node\Stmt\ClassMethod::class);
        $methodsInDesiredOrder = $this->getMethodsInDesiredOrder($node);
        $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($methodsInDesiredOrder, $currentMethodsOrder);
        // nothing to re-order
        if (!$this->orderChangeAnalyzer->hasOrderChanged($oldToNewKeys)) {
            return null;
        }
        $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
        return $node;
    }
    /**
     * @return string[]
     */
    private function getMethodsInDesiredOrder(\PhpParser\Node\Stmt\ClassLike $classLike) : array
    {
        $classMethodNames = $this->stmtVisibilitySorter->sortMethods($classLike);
        return $this->applyPreferredPosition($classMethodNames);
    }
    /**
     * @param string[] $classMethods
     * @return string[]
     */
    private function applyPreferredPosition(array $classMethods) : array
    {
        $mergedMethods = \array_merge(self::PREFERRED_ORDER, $classMethods);
        return \array_unique($mergedMethods);
    }
}
