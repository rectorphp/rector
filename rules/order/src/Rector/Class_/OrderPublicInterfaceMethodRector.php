<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtOrder;

/**
 * @see \Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector\OrderPublicInterfaceMethodRectorTest
 */
final class OrderPublicInterfaceMethodRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $methodOrderByInterfaces = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var StmtOrder
     */
    private $stmtOrder;

    /**
     * @param string[][] $methodOrderByInterfaces
     */
    public function __construct(
        ClassManipulator $classManipulator,
        StmtOrder $stmtOrder,
        array $methodOrderByInterfaces = []
    ) {
        $this->classManipulator = $classManipulator;
        $this->methodOrderByInterfaces = $methodOrderByInterfaces;
        $this->stmtOrder = $stmtOrder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Order public methods required by interface in custom orderer', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass implements FoodRecipeInterface
{
    public function process()
    {
    }

    public function getDescription()
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass implements FoodRecipeInterface
{
    public function getDescription()
    {
    }
    public function process()
    {
    }
}
PHP
           , [
               '$methodOrderByInterfaces' => [
                   'FoodRecipeInterface' => ['getDescription', 'process'],
               ],
           ]
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
        $implementedInterfaces = $this->classManipulator->getImplementedInterfaceNames($node);
        $publicMethodOrderByKey = $this->collectPublicMethods($node);

        foreach ($implementedInterfaces as $implementedInterface) {
            $methodOrder = $this->methodOrderByInterfaces[$implementedInterface] ?? null;
            if ($methodOrder === null) {
                continue;
            }

            $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($publicMethodOrderByKey, $methodOrder);
            $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);

            break;
        }

        return $node;
    }

    /**
     * @return string[]
     */
    private function collectPublicMethods(Class_ $class): array
    {
        $publicClassMethods = [];

        foreach ($class->stmts as $key => $classStmt) {
            if (! $classStmt instanceof ClassMethod) {
                continue;
            }

            if (! $classStmt->isPublic()) {
                continue;
            }

            /** @var string $classMethodName */
            $classMethodName = $this->getName($classStmt);
            $publicClassMethods[$key] = $classMethodName;
        }

        return $publicClassMethods;
    }
}
