<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\SymfonyPHPUnit\Naming\ServiceNaming;
use Rector\SymfonyPHPUnit\Node\KernelTestCaseNodeAnalyzer;
use Rector\SymfonyPHPUnit\Node\KernelTestCaseNodeFactory;
use Rector\SymfonyPHPUnit\SelfContainerMethodCallCollector;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @see \Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\SelfContainerGetMethodCallFromTestToSetUpMethodRectorTest
 */
final class SelfContainerGetMethodCallFromTestToSetUpMethodRector extends AbstractRector
{
    /**
     * @var KernelTestCaseNodeAnalyzer
     */
    private $kernelTestCaseNodeAnalyzer;

    /**
     * @var KernelTestCaseNodeFactory
     */
    private $kernelTestCaseNodeFactory;

    /**
     * @var ServiceNaming
     */
    private $serviceNaming;

    /**
     * @var SelfContainerMethodCallCollector
     */
    private $selfContainerMethodCallCollector;

    public function __construct(
        KernelTestCaseNodeAnalyzer $kernelTestCaseNodeAnalyzer,
        KernelTestCaseNodeFactory $kernelTestCaseNodeFactory,
        ServiceNaming $serviceNaming,
        SelfContainerMethodCallCollector $selfContainerMethodCallCollector
    ) {
        $this->kernelTestCaseNodeAnalyzer = $kernelTestCaseNodeAnalyzer;
        $this->kernelTestCaseNodeFactory = $kernelTestCaseNodeFactory;
        $this->selfContainerMethodCallCollector = $selfContainerMethodCallCollector;
        $this->serviceNaming = $serviceNaming;
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
        if ($node->extends === null) {
            return null;
        }

        if (! $this->isObjectType($node, KernelTestCase::class)) {
            return null;
        }

        // 1. find self::$container->get(x) that are called more than in 1 method
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
        $formerVariablesByMethods = $this->removeAndCollectFormerAssignedVariables($node);

        // 5. replace former variables by $this->someProperty
        $this->replaceFormerVariablesWithPropertyFetch($node, $formerVariablesByMethods);

        return $node;
    }

    /**
     * @return string[][]
     */
    private function removeAndCollectFormerAssignedVariables(Class_ $class): array
    {
        $formerVariablesByMethods = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            &$formerVariablesByMethods
        ): ?PropertyFetch {
            if (! $node instanceof MethodCall) {
                return null;
            }

            // skip setUp() method
            $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);
            if ($methodName === 'setUp' || $methodName === null) {
                return null;
            }

            if (! $this->kernelTestCaseNodeAnalyzer->isSelfContainerGetMethodCall($node)) {
                return null;
            }

            $type = $this->getValue($node->args[0]->value);
            if ($type === null) {
                return null;
            }

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Assign) {
                $variableName = $this->getName($parentNode->var);
                if ($variableName === null) {
                    return null;
                }

                $formerVariablesByMethods[$methodName][$variableName] = $type;

                $this->removeNode($parentNode);
                return null;
            }

            $propertyName = $this->serviceNaming->resolvePropertyNameFromServiceType($type);

            return new PropertyFetch(new Variable('this'), $propertyName);
        });

        return $formerVariablesByMethods;
    }

    /**
     * @param string[][] $formerVariablesByMethods
     */
    private function replaceFormerVariablesWithPropertyFetch(Class_ $class, array $formerVariablesByMethods): void
    {
        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            $formerVariablesByMethods
        ): ?PropertyFetch {
            if (! $node instanceof Variable) {
                return null;
            }

            /** @var string $methodName */
            $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);
            $variableName = $this->getName($node);
            if ($variableName === null) {
                return null;
            }

            if (! isset($formerVariablesByMethods[$methodName][$variableName])) {
                return null;
            }

            $serviceType = $formerVariablesByMethods[$methodName][$variableName];
            $propertyName = $this->serviceNaming->resolvePropertyNameFromServiceType($serviceType);

            return new PropertyFetch(new Variable('this'), $propertyName);
        });
    }
}
