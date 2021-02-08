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
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\RemovingStatic\StaticTypesInClassResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Depends on @see PassFactoryToUniqueObjectRector
 *
 * @see \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\PassFactoryToEntityRectorTest
 */
final class NewUniqueObjectToEntityFactoryRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
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
     * @var string[]
     */
    private $typesToServices = [];

    /**
     * @var string[]
     */
    private $classesUsingTypes = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var StaticTypesInClassResolver
     */
    private $staticTypesInClassResolver;

    public function __construct(PropertyNaming $propertyNaming, StaticTypesInClassResolver $staticTypesInClassResolver)
    {
        $this->propertyNaming = $propertyNaming;
        $this->staticTypesInClassResolver = $staticTypesInClassResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert new X to new factories', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
<?php

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
        $this->matchedObjectTypes = [];

        // collect classes with new to factory in all classes
        $classesUsingTypes = $this->resolveClassesUsingTypes();

        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use (
            $classesUsingTypes
        ): ?MethodCall {
            if (! $node instanceof New_) {
                return null;
            }

            $class = $this->getName($node->class);
            if ($class === null) {
                return null;
            }

            if (! in_array($class, $classesUsingTypes, true)) {
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

            $this->addConstructorDependencyToClass($node, $propertyType, $propertyName);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->typesToServices = $configuration[self::TYPES_TO_SERVICES] ?? [];
    }

    /**
     * @return string[]
     */
    private function resolveClassesUsingTypes(): array
    {
        if ($this->classesUsingTypes !== []) {
            return $this->classesUsingTypes;
        }

        // temporary
        $classes = $this->nodeRepository->getClasses();
        if ($classes === []) {
            return [];
        }

        foreach ($classes as $class) {
            $hasTypes = (bool) $this->staticTypesInClassResolver->collectStaticCallTypeInClass(
                $class,
                $this->typesToServices
            );
            if ($hasTypes) {
                $name = $this->getName($class);
                if ($name === null) {
                    throw new ShouldNotHappenException();
                }
                $this->classesUsingTypes[] = $name;
            }
        }

        $this->classesUsingTypes = array_unique($this->classesUsingTypes);

        return $this->classesUsingTypes;
    }
}
