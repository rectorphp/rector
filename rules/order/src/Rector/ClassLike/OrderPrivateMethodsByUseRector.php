<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtOrder;
use Rector\Order\ValueObject\SortedClassMethodsAndOriginalClassMethods;

/**
 * @see \Rector\Order\Tests\Rector\ClassLike\OrderPrivateMethodsByUseRector\OrderPrivateMethodsByUseRectorTest
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

        $sortedClassMethodsAndOriginalClassMethods = $this->getPrivateMethodCallOrderAndClassMethods($node);

        // order is correct, nothing to change
        if ($sortedClassMethodsAndOriginalClassMethods->hasOrderChanged()) {
            return null;
        }

        // different private method count, one of them is dead probably
        if (! $sortedClassMethodsAndOriginalClassMethods->hasIdenticalClassMethodCount()) {
            return null;
        }

        $attempt = 0;
        while (! $sortedClassMethodsAndOriginalClassMethods->hasOrderSame()) {
            $attempt++;
            if ($attempt >= self::MAX_ATTEMTPS) {
                throw new ShouldNotHappenException('Number of attempts to reorder the methods exceeded');
            }

            $oldToNewKeys = $this->stmtOrder->createOldToNewKeys(
                $sortedClassMethodsAndOriginalClassMethods->getSortedClassMethods(),
                $sortedClassMethodsAndOriginalClassMethods->getOriginalClassMethods()
            );

            /** @var Class_ $node */
            $node = $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);

            $sortedClassMethodsAndOriginalClassMethods = $this->getPrivateMethodCallOrderAndClassMethods($node);
        }

        return $node;
    }

    private function getPrivateMethodCallOrderAndClassMethods(
        ClassLike $classLike
    ): SortedClassMethodsAndOriginalClassMethods {
        return new SortedClassMethodsAndOriginalClassMethods(
            $this->getLocalPrivateMethodCallOrder($classLike),
            $this->resolvePrivateClassMethods($classLike)
        );
    }

    /**
     * @return array<int, string>
     */
    private function getLocalPrivateMethodCallOrder(ClassLike $classLike): array
    {
        $localPrivateMethodCallInOrder = [];

        $this->traverseNodesWithCallable($classLike->getMethods(), function (Node $node) use (
            &$localPrivateMethodCallInOrder,
            $classLike
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

            $classMethod = $classLike->getMethod($methodName);
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
     * @return array<int, string>
     */
    private function resolvePrivateClassMethods(ClassLike $classLike): array
    {
        $privateClassMethods = [];

        foreach ($classLike->stmts as $key => $classStmt) {
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
