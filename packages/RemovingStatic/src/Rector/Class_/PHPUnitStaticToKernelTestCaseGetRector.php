<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\VariableInfo;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

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
        CallableNodeTraverser $callableNodeTraverser,
        ClassManipulator $classManipulator,
        PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        array $staticClassTypes = [],
        string $kernelTestCaseClass = 'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase'
    ) {
        $this->staticClassTypes = $staticClassTypes;
        $this->propertyNaming = $propertyNaming;
        $this->kernelTestCaseClass = $kernelTestCaseClass;
        $this->callableNodeTraverser = $callableNodeTraverser;
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
        return [Node\Expr\StaticCall::class, Node\Stmt\Class_::class];
    }

    /**
     * @param Node\Expr\StaticCall|Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // skip yourself
        $this->newProperties = [];

        if ($node instanceof Node\Stmt\Class_) {
            if ($this->isTypes($node, $this->staticClassTypes)) {
                return null;
            }

            return $this->processClass($node);
        }

        return $this->processStaticCall($node);
    }

    private function processClass(Node\Stmt\Class_ $class): ?Node\Stmt\Class_
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

    private function processStaticCall(Node\Expr\StaticCall $staticCall): ?Node\Expr\MethodCall
    {
        /** @var Node\Stmt\Class_|null $class */
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
    private function collectNewProperties(Node\Stmt\Class_ $class): array
    {
        $this->newProperties = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node): void {
            if (! $node instanceof Node\Expr\StaticCall) {
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

    private function createPropertyFromType(string $type): Node\Stmt\Property
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($type);

        return $this->nodeFactory->createPrivatePropertyFromVariableInfo(new VariableInfo($propertyName, $type));
    }

    private function convertStaticCallToPropertyMethodCall(
        Node\Expr\StaticCall $staticCall,
        string $type
    ): Node\Expr\MethodCall {
        // create "$this->someService" instead
        $propertyName = $this->propertyNaming->fqnToVariableName($type);
        $propertyFetch = new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $propertyName);

        // turn static call to method on property call
        $methodCall = new Node\Expr\MethodCall($propertyFetch, $staticCall->name);
        $methodCall->args = $staticCall->args;

        return $methodCall;
    }

    private function createContainerGetTypeMethodCall(string $type): Node\Expr\MethodCall
    {
        $containerProperty = new Node\Expr\StaticPropertyFetch(new Node\Name('self'), 'container');
        $getMethodCall = new Node\Expr\MethodCall($containerProperty, 'get');
        $getMethodCall->args[] = new Node\Arg(new Node\Expr\ClassConstFetch(new Node\Name\FullyQualified(
            $type
        ), 'class'));

        return $getMethodCall;
    }

    /**
     * @param string[] $newProperties
     */
    private function addNewPropertiesToClass(Node\Stmt\Class_ $class, array $newProperties): Node\Stmt\Class_
    {
        $properties = [];
        foreach ($newProperties as $type) {
            $properties[] = $this->createPropertyFromType($type);
        }

        // add property to the start of the class
        $class->stmts = array_merge($properties, $class->stmts);

        return $class;
    }

    private function createContainerGetTypeToPropertyAssign(string $type): Node\Stmt\Expression
    {
        $getMethodCall = $this->createContainerGetTypeMethodCall($type);

        $propertyName = $this->propertyNaming->fqnToVariableName($type);
        $propertyFetch = new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $propertyName);

        $assign = new Node\Expr\Assign($propertyFetch, $getMethodCall);

        return new Node\Stmt\Expression($assign);
    }

    private function getParentSetUpStaticCallPosition(Node\Stmt\ClassMethod $setupClassMethod): ?int
    {
        foreach ((array) $setupClassMethod->stmts as $position => $methodStmt) {
            if ($methodStmt instanceof Node\Stmt\Expression) {
                $methodStmt = $methodStmt->expr;
            }

            if (! $methodStmt instanceof Node\Expr\StaticCall) {
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

    private function createParentSetUpStaticCall(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(new Node\Expr\StaticCall(new Node\Name('parent'), 'setUp'));
    }

    private function processPHPUnitClass(Node\Stmt\Class_ $class): ?Node\Stmt\Class_
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
            if ($setupClassMethod) {
                $this->updateSetUpMethod($setupClassMethod, $parentSetupStaticCall, $assign);
            } else {
                $setUpMethod = $this->createSetUpMethod($parentSetupStaticCall, $assign);
                $this->classManipulator->addAsFirstMethod($class, $setUpMethod);
            }
        }

        // update parent clsas if not already
        if (! $this->isType($class, $this->kernelTestCaseClass)) {
            $class->extends = new Node\Name\FullyQualified($this->kernelTestCaseClass);
        }

        return $class;
    }

    private function createSetUpMethod(
        Node\Stmt\Expression $parentSetupStaticCall,
        Node\Stmt\Expression $assign
    ): Node\Stmt\ClassMethod {
        $classMethodBuilder = $this->builderFactory->method('setUp')
            ->makeProtected();

        $classMethodBuilder->addStmt($parentSetupStaticCall);
        $classMethodBuilder->addStmt($assign);

        $classMethod = $classMethodBuilder->getNode();

        $this->phpUnitTypeDeclarationDecorator->decorate($classMethod);
        return $classMethod;
    }

    private function updateSetUpMethod(
        Node\Stmt\ClassMethod $setupClassMethod,
        Node\Stmt\Expression $parentSetupStaticCall,
        Node\Stmt\Expression $assign
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
