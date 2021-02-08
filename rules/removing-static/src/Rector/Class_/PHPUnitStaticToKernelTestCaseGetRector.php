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
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory;
use Rector\RemovingStatic\NodeFactory\SetUpFactory;
use Rector\RemovingStatic\ValueObject\PHPUnitClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\PHPUnitStaticToKernelTestCaseGetRectorTest
 */
final class PHPUnitStaticToKernelTestCaseGetRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const STATIC_CLASS_TYPES = 'static_class_types';

    /**
     * @var mixed[]
     */
    private $staticClassTypes = [];

    /**
     * @var ObjectType[]
     */
    private $newProperties = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    /**
     * @var SetUpClassMethodFactory
     */
    private $setUpClassMethodFactory;

    /**
     * @var SetUpFactory
     */
    private $setUpFactory;

    public function __construct(
        PropertyNaming $propertyNaming,
        ClassInsertManipulator $classInsertManipulator,
        SetUpClassMethodFactory $setUpClassMethodFactory,
        SetUpFactory $setUpFactory
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->setUpClassMethodFactory = $setUpClassMethodFactory;
        $this->setUpFactory = $setUpFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert static calls in PHPUnit test cases, to get() from the container of KernelTestCase', [
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
        $this->entityFactory = $this->getService(EntityFactory::class);
    }

    public function test()
    {
        $product = $this->entityFactory->create('product');
    }
}
CODE_SAMPLE
                ,
                [
                    self::STATIC_CLASS_TYPES => ['EntityFactory'],
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
            if ($this->isObjectTypes($node, $this->staticClassTypes)) {
                return null;
            }

            return $this->processClass($node);
        }

        return $this->processStaticCall($node);
    }

    public function configure(array $configuration): void
    {
        $this->staticClassTypes = $configuration[self::STATIC_CLASS_TYPES] ?? [];
    }

    private function processClass(Class_ $class): ?Class_
    {
        if ($this->isObjectType($class, PHPUnitClass::TEST_CASE)) {
            return $this->processPHPUnitClass($class);
        }

        // add property with the object
        $newPropertyObjectTypes = $this->collectNewPropertyObjectTypes($class);
        if ($newPropertyObjectTypes === []) {
            return null;
        }

        // add via constructor
        foreach ($newPropertyObjectTypes as $newPropertyObjectType) {
            $newPropertyName = $this->propertyNaming->fqnToVariableName($newPropertyObjectType);
            $this->addConstructorDependencyToClass($class, $newPropertyObjectType, $newPropertyName);
        }

        return $class;
    }

    private function processStaticCall(StaticCall $staticCall): ?MethodCall
    {
        $classLike = $staticCall->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        foreach ($this->staticClassTypes as $type) {
            $objectType = new ObjectType($type);
            if (! $this->isObjectType($staticCall->class, $objectType)) {
                continue;
            }

            return $this->convertStaticCallToPropertyMethodCall($staticCall, $objectType);
        }

        return null;
    }

    private function processPHPUnitClass(Class_ $class): ?Class_
    {
        // add property with the object
        $newPropertyTypes = $this->collectNewPropertyObjectTypes($class);
        if ($newPropertyTypes === []) {
            return null;
        }

        // add all properties to class
        $class = $this->addNewPropertiesToClass($class, $newPropertyTypes);

        $parentSetUpStaticCallExpression = $this->setUpFactory->createParentStaticCall();
        foreach ($newPropertyTypes as $type) {
            // container fetch assign
            $assign = $this->createContainerGetTypeToPropertyAssign($type);

            $setupClassMethod = $class->getMethod(MethodName::SET_UP);

            // get setup or create a setup add add it there
            if ($setupClassMethod !== null) {
                $this->updateSetUpMethod($setupClassMethod, $parentSetUpStaticCallExpression, $assign);
            } else {
                $setUpMethod = $this->setUpClassMethodFactory->createSetUpMethod([$assign]);
                $this->classInsertManipulator->addAsFirstMethod($class, $setUpMethod);
            }
        }

        // update parent clsas if not already
        if (! $this->isObjectType($class, 'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase')) {
            $class->extends = new FullyQualified('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase');
        }

        return $class;
    }

    /**
     * @return ObjectType[]
     */
    private function collectNewPropertyObjectTypes(Class_ $class): array
    {
        $this->newProperties = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node): void {
            if (! $node instanceof StaticCall) {
                return;
            }

            foreach ($this->staticClassTypes as $type) {
                $objectType = new ObjectType($type);
                if (! $this->isObjectType($node->class, $objectType)) {
                    continue;
                }

                $this->newProperties[] = $objectType;
            }
        });

        $this->newProperties = array_unique($this->newProperties);

        return $this->newProperties;
    }

    private function convertStaticCallToPropertyMethodCall(StaticCall $staticCall, ObjectType $objectType): MethodCall
    {
        // create "$this->someService" instead
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        // turn static call to method on property call
        $methodCall = new MethodCall($propertyFetch, $staticCall->name);
        $methodCall->args = $staticCall->args;

        return $methodCall;
    }

    /**
     * @param ObjectType[] $newProperties
     */
    private function addNewPropertiesToClass(Class_ $class, array $newProperties): Class_
    {
        $properties = [];
        foreach ($newProperties as $objectType) {
            $properties[] = $this->createPropertyFromType($objectType);
        }

        // add property to the start of the class
        $class->stmts = array_merge($properties, $class->stmts);

        return $class;
    }

    private function createContainerGetTypeToPropertyAssign(ObjectType $objectType): Expression
    {
        $getMethodCall = $this->createContainerGetTypeMethodCall($objectType);

        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        $assign = new Assign($propertyFetch, $getMethodCall);

        return new Expression($assign);
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

    private function createPropertyFromType(ObjectType $objectType): Property
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);

        return $this->nodeFactory->createPrivatePropertyFromNameAndType($propertyName, $objectType);
    }

    private function createContainerGetTypeMethodCall(ObjectType $objectType): MethodCall
    {
        $staticPropertyFetch = new StaticPropertyFetch(new Name('self'), 'container');
        $getMethodCall = new MethodCall($staticPropertyFetch, 'get');

        $className = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($objectType);
        if (! $className instanceof Name) {
            throw new ShouldNotHappenException();
        }

        $getMethodCall->args[] = new Arg(new ClassConstFetch($className, 'class'));

        return $getMethodCall;
    }

    private function getParentSetUpStaticCallPosition(ClassMethod $setupClassMethod): ?int
    {
        foreach ((array) $setupClassMethod->stmts as $position => $methodStmt) {
            if ($methodStmt instanceof Expression) {
                $methodStmt = $methodStmt->expr;
            }

            if (! $this->isStaticCallNamed($methodStmt, 'parent', MethodName::SET_UP)) {
                continue;
            }

            return $position;
        }

        return null;
    }
}
