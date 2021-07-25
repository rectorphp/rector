<?php

declare (strict_types=1);
namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Depends on @see PassFactoryToUniqueObjectRector
 *
 * @see \Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\PassFactoryToEntityRectorTest
 */
final class NewUniqueObjectToEntityFactoryRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPES_TO_SERVICES = 'types_to_services';
    /**
     * @var string
     */
    private const FACTORY = 'Factory';
    /**
     * @var ObjectType[]
     */
    private $matchedObjectTypes = [];
    /**
     * @var ObjectType[]
     */
    private $serviceObjectTypes = [];
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector)
    {
        $this->propertyNaming = $propertyNaming;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert new X to new factories', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return new AnotherClass;
    }
}

class AnotherClass
{
    public function someFun()
    {
        return StaticClass::staticMethod();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(AnotherClassFactory $anotherClassFactory)
    {
        $this->anotherClassFactory = $anotherClassFactory;
    }

    public function run()
    {
        return $this->anotherClassFactory->create();
    }
}

class AnotherClass
{
    public function someFun()
    {
        return StaticClass::staticMethod();
    }
}
CODE_SAMPLE
, [self::TYPES_TO_SERVICES => ['ClassName']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Stmt\Class_
    {
        $this->matchedObjectTypes = [];
        // collect classes with new to factory in all classes
        $this->traverseNodesWithCallable($node->stmts, function (\PhpParser\Node $node) : ?MethodCall {
            if (!$node instanceof \PhpParser\Node\Expr\New_) {
                return null;
            }
            $class = $this->getName($node->class);
            if ($class === null) {
                return null;
            }
            if (!$this->isClassMatching($class)) {
                return null;
            }
            $objectType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($class);
            $this->matchedObjectTypes[] = $objectType;
            $propertyName = $this->propertyNaming->fqnToVariableName($objectType) . self::FACTORY;
            $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $propertyName);
            return new \PhpParser\Node\Expr\MethodCall($propertyFetch, 'create', $node->args);
        });
        foreach ($this->matchedObjectTypes as $matchedObjectType) {
            $propertyName = $this->propertyNaming->fqnToVariableName($matchedObjectType) . self::FACTORY;
            $propertyType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($matchedObjectType->getClassName() . self::FACTORY);
            $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $propertyType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
            $this->propertyToAddCollector->addPropertyToClass($node, $propertyMetadata);
        }
        return $node;
    }
    /**
     * @param array<string, mixed[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $typesToServices = $configuration[self::TYPES_TO_SERVICES] ?? [];
        foreach ($typesToServices as $typeToService) {
            $this->serviceObjectTypes[] = new \PHPStan\Type\ObjectType($typeToService);
        }
    }
    private function isClassMatching(string $class) : bool
    {
        foreach ($this->serviceObjectTypes as $serviceObjectType) {
            if ($serviceObjectType->isInstanceOf($class)->yes()) {
                return \true;
            }
        }
        return \false;
    }
}
