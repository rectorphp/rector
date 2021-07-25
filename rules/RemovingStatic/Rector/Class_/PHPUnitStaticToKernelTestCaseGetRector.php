<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\RemovingStatic\NodeAnalyzer\SetUpClassMethodUpdater;
use Rector\RemovingStatic\NodeFactory\SelfContainerFactory;
use Rector\RemovingStatic\NodeFactory\SetUpFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\PHPUnitStaticToKernelTestCaseGetRectorTest
 */
final class PHPUnitStaticToKernelTestCaseGetRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const STATIC_CLASS_TYPES = 'static_class_types';

    /**
     * @var ObjectType[]
     */
    private array $staticObjectTypes = [];

    /**
     * @var ObjectType[]
     */
    private array $newPropertyObjectTypes = [];

    public function __construct(
        private PropertyNaming $propertyNaming,
        private ClassInsertManipulator $classInsertManipulator,
        private SetUpClassMethodFactory $setUpClassMethodFactory,
        private SetUpFactory $setUpFactory,
        private SelfContainerFactory $selfContainerFactory,
        private SetUpClassMethodUpdater $setUpClassMethodUpdater,
        private PropertyToAddCollector $propertyToAddCollector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert static calls in PHPUnit test cases, to get() from the container of KernelTestCase', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
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
     * @return array<class-string<Node>>
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
        $this->newPropertyObjectTypes = [];

        if ($node instanceof Class_) {
            if ($this->nodeTypeResolver->isObjectTypes($node, $this->staticObjectTypes)) {
                return null;
            }

            return $this->processClass($node);
        }

        return $this->processStaticCall($node);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
        $staticClassTypes = $configuration[self::STATIC_CLASS_TYPES] ?? [];
        foreach ($staticClassTypes as $staticClassType) {
            $this->staticObjectTypes[] = new ObjectType($staticClassType);
        }
    }

    private function processClass(Class_ $class): ?Class_
    {
        if ($this->isObjectType($class, new ObjectType('PHPUnit\Framework\TestCase'))) {
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

            $propertyMetadata = new PropertyMetadata(
                $newPropertyName,
                $newPropertyObjectType,
                Class_::MODIFIER_PRIVATE
            );
            $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        }

        return $class;
    }

    private function processStaticCall(StaticCall $staticCall): ?MethodCall
    {
        $classLike = $staticCall->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        foreach ($this->staticObjectTypes as $staticObjectType) {
            if (! $this->isObjectType($staticCall->class, $staticObjectType)) {
                continue;
            }

            return $this->convertStaticCallToPropertyMethodCall($staticCall, $staticObjectType);
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
        foreach ($newPropertyTypes as $newPropertyType) {
            // container fetch assign
            $assign = $this->createContainerGetTypeToPropertyAssign($newPropertyType);

            $setupClassMethod = $class->getMethod(MethodName::SET_UP);

            // get setup or create a setup add add it there
            if ($setupClassMethod !== null) {
                $this->setUpClassMethodUpdater->updateSetUpMethod(
                    $setupClassMethod,
                    $parentSetUpStaticCallExpression,
                    $assign
                );
            } else {
                $setUpMethod = $this->setUpClassMethodFactory->createSetUpMethod([$assign]);
                $this->classInsertManipulator->addAsFirstMethod($class, $setUpMethod);
            }
        }

        // update parent clsas if not already
        if (! $this->isObjectType($class, new ObjectType('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase'))) {
            $class->extends = new FullyQualified('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase');
        }

        return $class;
    }

    /**
     * @return ObjectType[]
     */
    private function collectNewPropertyObjectTypes(Class_ $class): array
    {
        $this->newPropertyObjectTypes = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node): void {
            if (! $node instanceof StaticCall) {
                return;
            }

            foreach ($this->staticObjectTypes as $staticObjectType) {
                if (! $this->isObjectType($node->class, $staticObjectType)) {
                    continue;
                }

                $this->newPropertyObjectTypes[] = $staticObjectType;
            }
        });

        $this->newPropertyObjectTypes = array_unique($this->newPropertyObjectTypes);

        return $this->newPropertyObjectTypes;
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
     * @param ObjectType[] $propertyTypes
     */
    private function addNewPropertiesToClass(Class_ $class, array $propertyTypes): Class_
    {
        $properties = [];
        foreach ($propertyTypes as $propertyType) {
            $propertyName = $this->propertyNaming->fqnToVariableName($propertyType);
            $properties[] = $this->nodeFactory->createPrivatePropertyFromNameAndType($propertyName, $propertyType);
        }

        // add property to the start of the class
        $class->stmts = array_merge($properties, $class->stmts);

        return $class;
    }

    private function createContainerGetTypeToPropertyAssign(ObjectType $objectType): Expression
    {
        $getMethodCall = $this->selfContainerFactory->createGetTypeMethodCall($objectType);

        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        $assign = new Assign($propertyFetch, $getMethodCall);

        return new Expression($assign);
    }
}
