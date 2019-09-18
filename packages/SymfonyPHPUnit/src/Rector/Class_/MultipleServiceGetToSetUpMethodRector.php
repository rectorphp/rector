<?php declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\MultipleServiceGetToSetUpMethodRectorTest
 */
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

        if (! $this->isObjectType($node, $this->kernelTestCaseClass)) {
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

        $classMethodBuilder = $this->builderFactory->method('setUp');
        $classMethodBuilder->makeProtected();
        $classMethodBuilder->addStmts($stmts);
        $classMethod = $classMethodBuilder->getNode();

        $this->phpUnitTypeDeclarationDecorator->decorate($classMethod);

        return $classMethod;
    }

    private function createSelfContainerGetWithTypeMethodCall(string $serviceType): MethodCall
    {
        $staticPropertyFetch = new StaticPropertyFetch(new Name('self'), 'container');

        $methodCall = new MethodCall($staticPropertyFetch, 'get');
        if (Strings::contains($serviceType, '_') && ! Strings::contains($serviceType, '\\')) {
            // keep string
            $getArgumentValue = new String_($serviceType);
        } else {
            $getArgumentValue = $this->createClassConstantReference($serviceType);
        }

        $methodCall->args[] = new Arg($getArgumentValue);

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
            $propertyName = $this->resolvePropertyNameFromServiceType($serviceType);

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
     * @return Property[]
     */
    private function createPrivatePropertiesFromTypes(Class_ $class, array $serviceTypes): array
    {
        $properties = [];

        /** @var string $className */
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($serviceTypes as $serviceType) {
            $propertyName = $this->resolvePropertyNameFromServiceType($serviceType);

            // skip existing properties
            if (property_exists($className, $propertyName)) {
                continue;
            }

            $serviceType = new ObjectType($serviceType);
            $properties[] = $this->nodeFactory->createPrivatePropertyFromNameAndType($propertyName, $serviceType);
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
            $serviceType = $this->getValue($node->args[0]->value);
            if ($this->shouldSkipServiceType($serviceType)) {
                return null;
            }

            $serviceTypes[] = $serviceType;
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

            $propertyName = $this->resolvePropertyNameFromServiceType($type);

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

            $propertyName = $this->resolvePropertyNameFromServiceType($serviceType);

            return new PropertyFetch(new Variable('this'), $propertyName);
        });
    }

    private function shouldSkipServiceType(string $serviceType): bool
    {
        return $serviceType === 'Symfony\Component\HttpFoundation\Session\SessionInterface';
    }

    private function resolvePropertyNameFromServiceType(string $serviceType): string
    {
        if (Strings::contains($serviceType, '_') && ! Strings::contains($serviceType, '\\')) {
            return $this->propertyNaming->underscoreToName($serviceType);
        }

        return $this->propertyNaming->fqnToVariableName(new ObjectType($serviceType));
    }
}
