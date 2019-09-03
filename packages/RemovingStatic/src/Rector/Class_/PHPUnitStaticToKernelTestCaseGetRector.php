<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\VariableInfo;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\PHPUnitStaticToKernelTestCaseGetRectorTest
 */
final class PHPUnitStaticToKernelTestCaseGetRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $staticClassTypes = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var string[]
     */
    private $newProperties = [];

    /**
     * @var string
     */
    private $kernelTestCaseClass;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var PHPUnitTypeDeclarationDecorator
     */
    private $phpUnitTypeDeclarationDecorator;

    /**
     * @param string[] $staticClassTypes
     */
    public function __construct(
        PropertyNaming $propertyNaming,
        ClassManipulator $classManipulator,
        PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        array $staticClassTypes = [],
        string $kernelTestCaseClass = 'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase'
    ) {
        $this->staticClassTypes = $staticClassTypes;
        $this->propertyNaming = $propertyNaming;
        $this->kernelTestCaseClass = $kernelTestCaseClass;
        $this->classManipulator = $classManipulator;
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert static calls in PHPUnit test cases, to get() from the container of KernelTestCase', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
<?php

use PHPUnit\Framework\TestCase;

final class SomeTestCase extends TestCase
{
    public function test()
    {
        $product = EntityFactory::create('product');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SomeTestCase extends KernelTestCase
{
    /**
     * @var EntityFactory
     */
    private $entityFactory;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->entityFactory = self::$container->get(EntityFactory::class);
    }

    public function test()
    {
        $product = $this->entityFactory->create('product');
    }
}
CODE_SAMPLE
                ,
                [
                    'staticClassTypes' => ['EntityFactory'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class, Class_::class];
    }

    /**
     * @param StaticCall|Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // skip yourself
        $this->newProperties = [];

        if ($node instanceof Class_) {
            if ($this->isTypes($node, $this->staticClassTypes)) {
                return null;
            }

            return $this->processClass($node);
        }

        return $this->processStaticCall($node);
    }

    private function processClass(Class_ $class): ?Class_
    {
        if ($this->isType($class, 'PHPUnit\Framework\TestCase')) {
            return $this->processPHPUnitClass($class);
        }

        // add property with the object
        $newPropertyTypes = $this->collectNewProperties($class);
        if ($newPropertyTypes === []) {
            return null;
        }

        // add via constructor
        foreach ($newPropertyTypes as $newPropertyType) {
            $newPropertyName = $this->propertyNaming->fqnToVariableName($newPropertyType);
            $this->addPropertyToClass($class, $newPropertyType, $newPropertyName);
        }

        return $class;
    }

    private function processStaticCall(StaticCall $staticCall): ?MethodCall
    {
        /** @var Class_|null $class */
        $class = $staticCall->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return null;
        }

        foreach ($this->staticClassTypes as $type) {
            if (! $this->isType($staticCall, $type)) {
                continue;
            }

            return $this->convertStaticCallToPropertyMethodCall($staticCall, $type);
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function collectNewProperties(Class_ $class): array
    {
        $this->newProperties = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node): void {
            if (! $node instanceof StaticCall) {
                return;
            }

            foreach ($this->staticClassTypes as $type) {
                if (! $this->isType($node, $type)) {
                    continue;
                }

                $this->newProperties[] = $type;
            }
        });

        $this->newProperties = array_unique($this->newProperties);

        return $this->newProperties;
    }

    private function createPropertyFromType(string $type): Property
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($type);

        return $this->nodeFactory->createPrivatePropertyFromVariableInfo(new VariableInfo($propertyName, $type));
    }

    private function convertStaticCallToPropertyMethodCall(StaticCall $staticCall, string $type): MethodCall
    {
        // create "$this->someService" instead
        $propertyName = $this->propertyNaming->fqnToVariableName($type);
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        // turn static call to method on property call
        $methodCall = new MethodCall($propertyFetch, $staticCall->name);
        $methodCall->args = $staticCall->args;

        return $methodCall;
    }

    private function createContainerGetTypeMethodCall(string $type): MethodCall
    {
        $containerProperty = new StaticPropertyFetch(new Name('self'), 'container');
        $getMethodCall = new MethodCall($containerProperty, 'get');
        $getMethodCall->args[] = new Arg(new ClassConstFetch(new FullyQualified($type), 'class'));

        return $getMethodCall;
    }

    /**
     * @param string[] $newProperties
     */
    private function addNewPropertiesToClass(Class_ $class, array $newProperties): Class_
    {
        $properties = [];
        foreach ($newProperties as $type) {
            $properties[] = $this->createPropertyFromType($type);
        }

        // add property to the start of the class
        $class->stmts = array_merge($properties, $class->stmts);

        return $class;
    }

    private function createContainerGetTypeToPropertyAssign(string $type): Expression
    {
        $getMethodCall = $this->createContainerGetTypeMethodCall($type);

        $propertyName = $this->propertyNaming->fqnToVariableName($type);
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        $assign = new Assign($propertyFetch, $getMethodCall);

        return new Expression($assign);
    }

    private function getParentSetUpStaticCallPosition(ClassMethod $setupClassMethod): ?int
    {
        foreach ((array) $setupClassMethod->stmts as $position => $methodStmt) {
            if ($methodStmt instanceof Expression) {
                $methodStmt = $methodStmt->expr;
            }

            if (! $methodStmt instanceof StaticCall) {
                continue;
            }

            if (! $this->isName($methodStmt->class, 'parent')) {
                continue;
            }

            if (! $this->isName($methodStmt->name, 'setUp')) {
                continue;
            }

            return $position;
        }

        return null;
    }

    private function createParentSetUpStaticCall(): Expression
    {
        return new Expression(new StaticCall(new Name('parent'), 'setUp'));
    }

    private function processPHPUnitClass(Class_ $class): ?Class_
    {
        // add property with the object
        $newProperties = $this->collectNewProperties($class);
        if ($newProperties === []) {
            return null;
        }

        // add all properties to class
        $class = $this->addNewPropertiesToClass($class, $newProperties);

        $parentSetupStaticCall = $this->createParentSetUpStaticCall();
        foreach ($newProperties as $type) {
            // container fetch assign
            $assign = $this->createContainerGetTypeToPropertyAssign($type);

            $setupClassMethod = $class->getMethod('setUp');

            // get setup or create a setup add add it there
            if ($setupClassMethod !== null) {
                $this->updateSetUpMethod($setupClassMethod, $parentSetupStaticCall, $assign);
            } else {
                $setUpMethod = $this->createSetUpMethod($parentSetupStaticCall, $assign);
                $this->classManipulator->addAsFirstMethod($class, $setUpMethod);
            }
        }

        // update parent clsas if not already
        if (! $this->isType($class, $this->kernelTestCaseClass)) {
            $class->extends = new FullyQualified($this->kernelTestCaseClass);
        }

        return $class;
    }

    private function createSetUpMethod(Expression $parentSetupStaticCall, Expression $assign): ClassMethod
    {
        $classMethodBuilder = $this->builderFactory->method('setUp')
            ->makeProtected();

        $classMethodBuilder->addStmt($parentSetupStaticCall);
        $classMethodBuilder->addStmt($assign);

        $classMethod = $classMethodBuilder->getNode();

        $this->phpUnitTypeDeclarationDecorator->decorate($classMethod);
        return $classMethod;
    }

    private function updateSetUpMethod(
        ClassMethod $setupClassMethod,
        Expression $parentSetupStaticCall,
        Expression $assign
    ): void {
        $parentSetUpStaticCallPosition = $this->getParentSetUpStaticCallPosition($setupClassMethod);
        if ($parentSetUpStaticCallPosition === null) {
            $setupClassMethod->stmts = array_merge([$parentSetupStaticCall, $assign], (array) $setupClassMethod->stmts);
        } else {
            assert($setupClassMethod->stmts !== null);
            array_splice($setupClassMethod->stmts, $parentSetUpStaticCallPosition + 1, 0, [$assign]);
        }
    }
}
