<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtOrder;

/**
 * @see \Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector\OrderPublicInterfaceMethodRectorTest
 */
final class OrderPublicInterfaceMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_ORDER_BY_INTERFACES = 'method_order_by_interfaces';

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

    public function __construct(ClassManipulator $classManipulator, StmtOrder $stmtOrder)
    {
        $this->classManipulator = $classManipulator;
        $this->stmtOrder = $stmtOrder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Order public methods required by interface in custom orderer', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass implements FoodRecipeInterface
{
    public function process()
    {
    }

    public function getDescription()
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass implements FoodRecipeInterface
{
    public function getDescription()
    {
    }
    public function process()
    {
    }
}
CODE_SAMPLE
           , [
            self::METHOD_ORDER_BY_INTERFACES => [
                'FoodRecipeInterface' => ['getDescription', 'process'],
            ],
        ]),
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
        if ($implementedInterfaces === []) {
            return null;
        }

        $publicMethodOrderByKey = $this->collectPublicMethods($node);

        foreach ($implementedInterfaces as $implementedInterface) {
            $methodOrder = $this->methodOrderByInterfaces[$implementedInterface] ?? null;
            if ($methodOrder === null) {
                continue;
            }

            $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($publicMethodOrderByKey, $methodOrder);

            // nothing to re-order
            if (array_keys($oldToNewKeys) === array_values($oldToNewKeys)) {
                return null;
            }

            $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);

            break;
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->methodOrderByInterfaces = $configuration[self::METHOD_ORDER_BY_INTERFACES] ?? [];
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
