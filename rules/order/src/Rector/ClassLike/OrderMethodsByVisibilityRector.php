<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtOrder;

/**
 * @see \Rector\Order\Tests\Rector\ClassLike\OrderMethodsByVisibilityRector\OrderMethodsByVisibilityRectorTest
 */
final class OrderMethodsByVisibilityRector extends AbstractRector
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

        $classMethods = [];
        $classMethodsByName = [];
        foreach ($node->stmts as $position => $classStmt) {
            if (! $classStmt instanceof ClassMethod) {
                continue;
            }

            /** @var string $classMethodName */
            $classMethodName = $this->getName($classStmt);
            $classMethodsByName[$position] = $classMethodName;

            $classMethods[$classMethodName]['name'] = $classMethodName;
            $classMethods[$classMethodName][self::VISIBILITY] = $this->getVisibilityOrder($classStmt);
            $classMethods[$classMethodName]['final'] = $classStmt->isFinal();
            $classMethods[$classMethodName]['static'] = $classStmt->isStatic();
            $classMethods[$classMethodName][self::POSITION] = $position;
        }

        $sortedProperties = $this->getMethodsSortedByVisibility($classMethods);
        $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($sortedProperties, $classMethodsByName);

        return $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
    }

    private function getVisibilityOrder(ClassMethod $classMethod): int
    {
        if ($classMethod->isPrivate()) {
            return 2;
        }

        if ($classMethod->isProtected()) {
            return 1;
        }

        return 0;
    }

    private function getMethodsSortedByVisibility(array $classMethods): array
    {
        uasort(
            $classMethods,
            function (array $firstArray, array $secondArray): int {
                return [
                    $firstArray[self::VISIBILITY],
                    $firstArray['static'],
                    $firstArray['final'],
                    $firstArray[self::POSITION],
                ] <=> [
                    $secondArray[self::VISIBILITY],
                    $secondArray['static'],
                    $secondArray['final'],
                    $secondArray[self::POSITION],
                ];
            }
        );

        return array_keys($classMethods);
    }
}
