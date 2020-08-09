<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
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
     * @var int
     */
    private const MAX_ATTEMTPS = 5;

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
        [$desiredPrivateMethodCallOrder, $privateClassMethodsByKey] = $this->getPrivateMethodCallOrderAndClassMethods(
            $node
        );

        // order is correct, nothing to change
        if ($privateClassMethodsByKey === $desiredPrivateMethodCallOrder) {
            return null;
        }

        // different private method count, one of them is dead probably
        if (count($desiredPrivateMethodCallOrder) !== count($privateClassMethodsByKey)) {
            return null;
        }

        $attempt = 0;
        while (array_values($desiredPrivateMethodCallOrder) !== array_values($privateClassMethodsByKey)) {
            $attempt++;
            if ($attempt >= self::MAX_ATTEMTPS) {
                throw new ShouldNotHappenException('Number of attempts to reorder the methods exceeded');
            }

            $oldToNewKeys = $this->stmtOrder->createOldToNewKeys(
                $desiredPrivateMethodCallOrder,
                $privateClassMethodsByKey
            );

            /** @var Class_ $node */
            $node = $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);

            [$desiredPrivateMethodCallOrder, $privateClassMethodsByKey] = $this->getPrivateMethodCallOrderAndClassMethods(
                $node
            );
        }

        return $node;
    }

    /**
     * @return array<int,array<int,string>>
     */
    private function getPrivateMethodCallOrderAndClassMethods(Class_ $class): array
    {
        return [$this->getLocalPrivateMethodCallOrder($class), $this->resolvePrivateClassMethods($class)];
    }

    /**
     * @return array<int,string>
     */
    private function getLocalPrivateMethodCallOrder(Class_ $class): array
    {
        $localPrivateMethodCallInOrder = [];

        $this->traverseNodesWithCallable($class->getMethods(), function (Node $node) use (
            &$localPrivateMethodCallInOrder,
            $class
        ) {
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

            $classMethod = $class->getMethod($methodName);
            if ($classMethod === null) {
                return null;
            }

            if ($classMethod->isPrivate()) {
                $localPrivateMethodCallInOrder[] = $methodName;
            }

            return null;
        });

        return array_unique($localPrivateMethodCallInOrder);
    }

    /**
     * @return array<int,string>
     */
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
