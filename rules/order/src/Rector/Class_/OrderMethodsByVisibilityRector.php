<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\Rector\AbstractConstantPropertyMethodOrderRector;

/**
 * @see \Rector\Order\Tests\Rector\Class_\OrderMethodsByVisibilityRector\OrderMethodsByVisibilityRectorTest
 */
final class OrderMethodsByVisibilityRector extends AbstractConstantPropertyMethodOrderRector
{
    /**
     * @var string[]
     */
    private const PREFERRED_ORDER = [
        '__construct',
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__serialize',
        '__unserialize',
        '__toString',
        '__invoke',
        '__set_state',
        '__clone',
        'setUpBeforeClass',
        'tearDownAfterClass',
        'setUp',
        'tearDown',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Orders method by visibility', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    protected function protectedFunctionName();
    private function privateFunctionName();
    public function publicFunctionName();
}
PHP

                ,
                <<<'PHP'
class SomeClass
{
    public function publicFunctionName();
    protected function protectedFunctionName();
    private function privateFunctionName();
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
        return [Class_::class, Trait_::class];
    }

    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $currentMethodsOrder = $this->stmtOrder->getStmtsOfTypeOrder($node, ClassMethod::class);
        $methodsInDesiredOrder = $this->getMethodsInDesiredOrder($node);

        $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($methodsInDesiredOrder, $currentMethodsOrder);

        // nothing to re-order
        if (! $this->hasOrderChanged($oldToNewKeys)) {
            return null;
        }

        return $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
    }

    /**
     * @return string[]
     */
    private function getMethodsInDesiredOrder(ClassLike $classLike): array
    {
        $classMethodNames = $this->stmtVisibilitySorter->sortMethods($classLike);
        return $this->applyPreferredPosition($classMethodNames);
    }

    /**
     * @param string[] $classMethods
     * @return string[]
     */
    private function applyPreferredPosition(array $classMethods): array
    {
        return array_unique(array_merge(self::PREFERRED_ORDER, $classMethods));
    }
}
