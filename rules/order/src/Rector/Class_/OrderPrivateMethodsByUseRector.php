<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtOrder;

/**
 * @see \Rector\Order\Tests\Rector\Class_\OrderPrivateMethodsByUseRector\OrderPrivateMethodsByUseRectorTest
 */
final class OrderPrivateMethodsByUseRector extends AbstractRector
{
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
        return new RectorDefinition('Order private methods in order of their use', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $this->call1();
        $this->call2();
    }

    private function call2()
    {
    }

    private function call1()
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $this->call1();
        $this->call2();
    }

    private function call1()
    {
    }

    private function call2()
    {
    }
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
        $desiredClassMethodOrder = $this->getLocalMethodCallOrder($node);
        $privateClassMethodsByKey = $this->resolvePrivateClassMethods($node);

        // order is correct, nothing to change
        if ($privateClassMethodsByKey === $desiredClassMethodOrder) {
            return null;
        }

        // different private method count, one of them is dead probably
        if (count($desiredClassMethodOrder) !== count($desiredClassMethodOrder)) {
            return null;
        }

        $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($desiredClassMethodOrder, $privateClassMethodsByKey);

        return $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
    }

    /**
     * @return string[]
     */
    private function getLocalMethodCallOrder(Class_ $class): array
    {
        $localMethodCallInOrder = [];

        $this->traverseNodesWithCallable($class->getMethods(), function (Node $node) use (&$localMethodCallInOrder) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isVariableName($node->var, 'this')) {
                return null;
            }

            $methodName = $this->getName($node->name);
            if ($methodName === null) {
                return null;
            }

            $localMethodCallInOrder[] = $methodName;

            return null;
        });

        return array_unique($localMethodCallInOrder);
    }

    private function resolvePrivateClassMethods(Class_ $class): array
    {
        $privateClassMethods = [];

        foreach ($class->stmts as $key => $classStmt) {
            if (! $classStmt instanceof ClassMethod) {
                continue;
            }

            if (! $classStmt->isPrivate()) {
                continue;
            }

            /** @var string $classMethodName */
            $classMethodName = $this->getName($classStmt);
            $privateClassMethods[$key] = $classMethodName;
        }

        return $privateClassMethods;
    }
}
