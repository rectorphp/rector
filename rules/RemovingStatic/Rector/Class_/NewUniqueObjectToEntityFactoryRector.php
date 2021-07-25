<?php

declare(strict_types=1);

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
final class NewUniqueObjectToEntityFactoryRector extends AbstractRector implements ConfigurableRectorInterface
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
    private array $matchedObjectTypes = [];

    /**
     * @var ObjectType[]
     */
    private array $serviceObjectTypes = [];

    public function __construct(
        private PropertyNaming $propertyNaming,
        private PropertyToAddCollector $propertyToAddCollector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert new X to new factories', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
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
                ,
                [
                    self::TYPES_TO_SERVICES => ['ClassName'],
                ]
            ), ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): Class_
    {
        $this->matchedObjectTypes = [];

        // collect classes with new to factory in all classes

        $this->traverseNodesWithCallable($node->stmts, function (Node $node): ?MethodCall {
            if (! $node instanceof New_) {
                return null;
            }

            $class = $this->getName($node->class);
            if ($class === null) {
                return null;
            }

            if (! $this->isClassMatching($class)) {
                return null;
            }

            $objectType = new FullyQualifiedObjectType($class);
            $this->matchedObjectTypes[] = $objectType;

            $propertyName = $this->propertyNaming->fqnToVariableName($objectType) . self::FACTORY;
            $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

            return new MethodCall($propertyFetch, 'create', $node->args);
        });

        foreach ($this->matchedObjectTypes as $matchedObjectType) {
            $propertyName = $this->propertyNaming->fqnToVariableName($matchedObjectType) . self::FACTORY;
            $propertyType = new FullyQualifiedObjectType($matchedObjectType->getClassName() . self::FACTORY);

            $propertyMetadata = new PropertyMetadata($propertyName, $propertyType, Class_::MODIFIER_PRIVATE);
            $this->propertyToAddCollector->addPropertyToClass($node, $propertyMetadata);
        }

        return $node;
    }

    /**
     * @param array<string, mixed[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $typesToServices = $configuration[self::TYPES_TO_SERVICES] ?? [];
        foreach ($typesToServices as $typeToService) {
            $this->serviceObjectTypes[] = new ObjectType($typeToService);
        }
    }

    private function isClassMatching(string $class): bool
    {
        foreach ($this->serviceObjectTypes as $serviceObjectType) {
            if ($serviceObjectType->isInstanceOf($class)->yes()) {
                return true;
            }
        }

        return false;
    }
}
