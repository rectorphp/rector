<?php

declare (strict_types=1);
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
use Rector\RemovingStatic\NodeAnalyzer\SetUpClassMethodUpdater;
use Rector\RemovingStatic\NodeFactory\SelfContainerFactory;
use Rector\RemovingStatic\NodeFactory\SetUpFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\PHPUnitStaticToKernelTestCaseGetRectorTest
 */
final class PHPUnitStaticToKernelTestCaseGetRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const STATIC_CLASS_TYPES = 'static_class_types';
    /**
     * @var ObjectType[]
     */
    private $staticObjectTypes = [];
    /**
     * @var ObjectType[]
     */
    private $newPropertyObjectTypes = [];
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @var \Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory
     */
    private $setUpClassMethodFactory;
    /**
     * @var \Rector\RemovingStatic\NodeFactory\SetUpFactory
     */
    private $setUpFactory;
    /**
     * @var \Rector\RemovingStatic\NodeFactory\SelfContainerFactory
     */
    private $selfContainerFactory;
    /**
     * @var \Rector\RemovingStatic\NodeAnalyzer\SetUpClassMethodUpdater
     */
    private $setUpClassMethodUpdater;
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory $setUpClassMethodFactory, \Rector\RemovingStatic\NodeFactory\SetUpFactory $setUpFactory, \Rector\RemovingStatic\NodeFactory\SelfContainerFactory $selfContainerFactory, \Rector\RemovingStatic\NodeAnalyzer\SetUpClassMethodUpdater $setUpClassMethodUpdater)
    {
        $this->propertyNaming = $propertyNaming;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->setUpClassMethodFactory = $setUpClassMethodFactory;
        $this->setUpFactory = $setUpFactory;
        $this->selfContainerFactory = $selfContainerFactory;
        $this->setUpClassMethodUpdater = $setUpClassMethodUpdater;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert static calls in PHPUnit test cases, to get() from the container of KernelTestCase', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
<?php

namespace RectorPrefix20210514;

use RectorPrefix20210514\PHPUnit\Framework\TestCase;
final class SomeTestCase extends \RectorPrefix20210514\PHPUnit\Framework\TestCase
{
    public function test()
    {
        $product = \RectorPrefix20210514\EntityFactory::create('product');
    }
}
\class_alias('SomeTestCase', 'SomeTestCase', \false);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
, [self::STATIC_CLASS_TYPES => ['EntityFactory']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param StaticCall|Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        // skip yourself
        $this->newPropertyObjectTypes = [];
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
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
    public function configure(array $configuration) : void
    {
        $staticClassTypes = $configuration[self::STATIC_CLASS_TYPES] ?? [];
        foreach ($staticClassTypes as $staticClassType) {
            $this->staticObjectTypes[] = new \PHPStan\Type\ObjectType($staticClassType);
        }
    }
    private function processClass(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node\Stmt\Class_
    {
        if ($this->isObjectType($class, new \PHPStan\Type\ObjectType('PHPUnit\\Framework\\TestCase'))) {
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
    private function processStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        $classLike = $staticCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        foreach ($this->staticObjectTypes as $staticObjectType) {
            if (!$this->isObjectType($staticCall->class, $staticObjectType)) {
                continue;
            }
            return $this->convertStaticCallToPropertyMethodCall($staticCall, $staticObjectType);
        }
        return null;
    }
    private function processPHPUnitClass(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node\Stmt\Class_
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
            $setupClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::SET_UP);
            // get setup or create a setup add add it there
            if ($setupClassMethod !== null) {
                $this->setUpClassMethodUpdater->updateSetUpMethod($setupClassMethod, $parentSetUpStaticCallExpression, $assign);
            } else {
                $setUpMethod = $this->setUpClassMethodFactory->createSetUpMethod([$assign]);
                $this->classInsertManipulator->addAsFirstMethod($class, $setUpMethod);
            }
        }
        // update parent clsas if not already
        if (!$this->isObjectType($class, new \PHPStan\Type\ObjectType('Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase'))) {
            $class->extends = new \PhpParser\Node\Name\FullyQualified('Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase');
        }
        return $class;
    }
    /**
     * @return ObjectType[]
     */
    private function collectNewPropertyObjectTypes(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $this->newPropertyObjectTypes = [];
        $this->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) : void {
            if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return;
            }
            foreach ($this->staticObjectTypes as $staticObjectType) {
                if (!$this->isObjectType($node->class, $staticObjectType)) {
                    continue;
                }
                $this->newPropertyObjectTypes[] = $staticObjectType;
            }
        });
        $this->newPropertyObjectTypes = \array_unique($this->newPropertyObjectTypes);
        return $this->newPropertyObjectTypes;
    }
    private function convertStaticCallToPropertyMethodCall(\PhpParser\Node\Expr\StaticCall $staticCall, \PHPStan\Type\ObjectType $objectType) : \PhpParser\Node\Expr\MethodCall
    {
        // create "$this->someService" instead
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $propertyName);
        // turn static call to method on property call
        $methodCall = new \PhpParser\Node\Expr\MethodCall($propertyFetch, $staticCall->name);
        $methodCall->args = $staticCall->args;
        return $methodCall;
    }
    /**
     * @param ObjectType[] $propertyTypes
     */
    private function addNewPropertiesToClass(\PhpParser\Node\Stmt\Class_ $class, array $propertyTypes) : \PhpParser\Node\Stmt\Class_
    {
        $properties = [];
        foreach ($propertyTypes as $propertyType) {
            $propertyName = $this->propertyNaming->fqnToVariableName($propertyType);
            $properties[] = $this->nodeFactory->createPrivatePropertyFromNameAndType($propertyName, $propertyType);
        }
        // add property to the start of the class
        $class->stmts = \array_merge($properties, $class->stmts);
        return $class;
    }
    private function createContainerGetTypeToPropertyAssign(\PHPStan\Type\ObjectType $objectType) : \PhpParser\Node\Stmt\Expression
    {
        $getMethodCall = $this->selfContainerFactory->createGetTypeMethodCall($objectType);
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $propertyName);
        $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, $getMethodCall);
        return new \PhpParser\Node\Stmt\Expression($assign);
    }
}
