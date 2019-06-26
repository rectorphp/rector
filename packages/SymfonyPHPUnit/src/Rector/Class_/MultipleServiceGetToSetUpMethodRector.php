<?php declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\VariableInfo;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class MultipleServiceGetToSetUpMethodRector extends AbstractRector
{
    /**
     * @var string
     */
    private const KERNEL_TEST_CASE_CLASS = 'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase';

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var PHPUnitTypeDeclarationDecorator
     */
    private $phpUnitTypeDeclarationDecorator;

    /**
     * @var string
     */
    private $kernelTestCaseClass;

    public function __construct(
        PropertyNaming $propertyNaming,
        PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        string $kernelTestCaseClass = self::KERNEL_TEST_CASE_CLASS
    ) {
        $this->kernelTestCaseClass = $kernelTestCaseClass;
        $this->propertyNaming = $propertyNaming;
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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

        if (! $this->isType($node, $this->kernelTestCaseClass)) {
            return null;
        }

        // 1. find self::$container->get(x) that are called more than in 1 method
        $serviceTypes = $this->collectContainerGetServiceTypes($node);
        if (count($serviceTypes) === 0) {
            return null;
        }

        // 2. put them to setUp() method
        $setUpClassMethod = $node->getMethod('setUp');
        if ($setUpClassMethod === null) {
            $setUpClassMethod = $this->createSetUpClassMethodWithGetTypes($node, $serviceTypes);
            if ($setUpClassMethod !== null) {
                $node->stmts = array_merge([$setUpClassMethod], $node->stmts);
            }
        } else {
            $assigns = $this->createSelfContainerGetWithTypeAssigns($node, $serviceTypes);
            $setUpClassMethod->stmts = array_merge((array) $setUpClassMethod->stmts, $assigns);
        }

        // 3. create private properties with this types
        $privateProperties = $this->createPrivatePropertiesFromTypes($node, $serviceTypes);
        $node->stmts = array_merge($privateProperties, $node->stmts);

        // 4. remove old in-method $property assigns
        $formerVariablesByMethods = $this->removeAndCollectFormerAssignedVariables($node);

        // 5. replace former variables by $this->someProperty
        $this->replaceFormerVariablesWithPropertyFetch($node, $formerVariablesByMethods);

        return $node;
    }

    private function isSelfContainerGetMethodCall(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof StaticPropertyFetch) {
            return false;
        }

        if (! $this->isName($node->var->class, 'self')) {
            return false;
        }

        if (! $this->isName($node->var->name, 'container')) {
            return false;
        }

        return $this->isName($node->name, 'get');
    }

    /**
     * @param string[] $serviceTypes
     */
    private function createSetUpClassMethodWithGetTypes(Class_ $class, array $serviceTypes): ?ClassMethod
    {
        $assigns = $this->createSelfContainerGetWithTypeAssigns($class, $serviceTypes);
        if (count($assigns) === 0) {
            return null;
        }

        $stmts = array_merge([new StaticCall(new Name('parent'), 'setUp')], $assigns);

        /** @var ClassMethod $node */
        $classMethod = $this->builderFactory->method('setUp')
            ->makeProtected()
            ->addStmts($stmts)
            ->getNode();

        $this->phpUnitTypeDeclarationDecorator->decorate($classMethod);

        return $classMethod;
    }

    private function createSelfContainerGetWithTypeMethodCall(string $serviceType): MethodCall
    {
        $staticPropertyFetch = new StaticPropertyFetch(new Name('self'), 'container');

        $methodCall = new MethodCall($staticPropertyFetch, 'get');
        $methodCall->args[] = new Arg($this->createClassConstantReference($serviceType));

        return $methodCall;
    }

    /**
     * @param string[] $serviceTypes
     *
     * @return Expression[]
     *
     * E.g. "['SomeService']" → "$this->someService = self::$container->get(SomeService::class);"
     */
    private function createSelfContainerGetWithTypeAssigns(Class_ $class, array $serviceTypes): array
    {
        $stmts = [];

        /** @var string $className */
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($serviceTypes as $serviceType) {
            $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);

            // skip existing properties
            if (property_exists($className, $propertyName)) {
                continue;
            }

            $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

            $methodCall = $this->createSelfContainerGetWithTypeMethodCall($serviceType);

            $assign = new Assign($propertyFetch, $methodCall);
            $stmts[] = new Expression($assign);
        }

        return $stmts;
    }

    /**
     * @param string[] $serviceTypes
     *
     * @return Property[]
     */
    private function createPrivatePropertiesFromTypes(Class_ $class, array $serviceTypes): array
    {
        $properties = [];

        /** @var string $className */
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($serviceTypes as $serviceType) {
            $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);

            // skip existing properties
            if (property_exists($className, $propertyName)) {
                continue;
            }

            $variableInfo = new VariableInfo($propertyName, $serviceType);
            $properties[] = $this->nodeFactory->createPrivatePropertyFromVariableInfo($variableInfo);
        }

        return $properties;
    }

    /**
     * @return string[]
     */
    private function collectContainerGetServiceTypes(Class_ $class): array
    {
        $serviceTypes = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (&$serviceTypes) {
            if (! $this->isSelfContainerGetMethodCall($node)) {
                return null;
            }

            // skip setUp() method
            $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);
            if ($methodName === 'setUp' || $methodName === null) {
                return null;
            }

            /** @var MethodCall $node */
            $serviceTypes[] = $this->getValue($node->args[0]->value);
        });

        return array_unique($serviceTypes);
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

            if (! $this->isSelfContainerGetMethodCall($node)) {
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

            $propertyName = $this->propertyNaming->fqnToVariableName($type);

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

            $type = $formerVariablesByMethods[$methodName][$variableName];
            $propertyName = $this->propertyNaming->fqnToVariableName($type);

            return new PropertyFetch(new Variable('this'), $propertyName);
        });
    }
}
