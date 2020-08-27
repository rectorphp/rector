<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PHPUnit\Manipulator\OnContainerGetCallManipulator;
use Rector\SymfonyPHPUnit\Node\KernelTestCaseNodeFactory;
use Rector\SymfonyPHPUnit\SelfContainerMethodCallCollector;

/**
 * @see \Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\SelfContainerGetMethodCallFromTestToSetUpMethodRectorTest
 */
final class SelfContainerGetMethodCallFromTestToSetUpMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var KernelTestCaseNodeFactory
     */
    private $kernelTestCaseNodeFactory;

    /**
     * @var SelfContainerMethodCallCollector
     */
    private $selfContainerMethodCallCollector;

    /**
     * @var OnContainerGetCallManipulator
     */
    private $onContainerGetCallManipulator;

    public function __construct(
        KernelTestCaseNodeFactory $kernelTestCaseNodeFactory,
        OnContainerGetCallManipulator $onContainerGetCallManipulator,
        SelfContainerMethodCallCollector $selfContainerMethodCallCollector
    ) {
        $this->kernelTestCaseNodeFactory = $kernelTestCaseNodeFactory;
        $this->selfContainerMethodCallCollector = $selfContainerMethodCallCollector;
        $this->onContainerGetCallManipulator = $onContainerGetCallManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move self::$container service fetching from test methods up to setUp method', [
            new CodeSample(
                <<<'PHP'
use ItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SomeTest extends KernelTestCase
{
    public function testOne()
    {
        $itemRepository = self::$container->get(ItemRepository::class);
        $itemRepository->doStuff();
    }

    public function testTwo()
    {
        $itemRepository = self::$container->get(ItemRepository::class);
        $itemRepository->doAnotherStuff();
    }
}
PHP
                ,
                <<<'PHP'
use ItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SomeTest extends KernelTestCase
{
    /**
     * @var \ItemRepository
     */
    private $itemRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->itemRepository = self::$container->get(ItemRepository::class);
    }

    public function testOne()
    {
        $this->itemRepository->doStuff();
    }

    public function testTwo()
    {
        $this->itemRepository->doAnotherStuff();
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        // 1. find self::$container->get(<X>)
        $serviceTypes = $this->selfContainerMethodCallCollector->collectContainerGetServiceTypes($node);
        if (count($serviceTypes) === 0) {
            return null;
        }

        // 2. put them to setUp() method
        $setUpClassMethod = $node->getMethod('setUp');
        if ($setUpClassMethod === null) {
            $setUpClassMethod = $this->kernelTestCaseNodeFactory->createSetUpClassMethodWithGetTypes(
                $node,
                $serviceTypes
            );
            if ($setUpClassMethod !== null) {
                $node->stmts = array_merge([$setUpClassMethod], $node->stmts);
            }
        } else {
            $assigns = $this->kernelTestCaseNodeFactory->createSelfContainerGetWithTypeAssigns($node, $serviceTypes);
            $setUpClassMethod->stmts = array_merge((array) $setUpClassMethod->stmts, $assigns);
        }

        // 3. create private properties with this types
        $privateProperties = $this->kernelTestCaseNodeFactory->createPrivatePropertiesFromTypes($node, $serviceTypes);
        $node->stmts = array_merge($privateProperties, $node->stmts);

        // 4. remove old in-method $property assigns
        $this->onContainerGetCallManipulator->removeAndCollectFormerAssignedVariables($node);

        // 5. replace former variables by $this->someProperty
        $this->onContainerGetCallManipulator->replaceFormerVariablesWithPropertyFetch($node);

        return $node;
    }
}
