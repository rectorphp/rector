<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\RemovingStatic\Printer\FactoryClassPrinter;
use Rector\RemovingStatic\StaticTypesInClassResolver;
use Rector\RemovingStatic\UniqueObjectFactoryFactory;
use Rector\RemovingStatic\UniqueObjectOrServiceDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\PassFactoryToEntityRectorTest
 */
final class PassFactoryToUniqueObjectRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const TYPES_TO_SERVICES = 'types_to_services';

    /**
     * @var ObjectType[]
     */
    private array $serviceObjectTypes = [];

    public function __construct(
        private StaticTypesInClassResolver $staticTypesInClassResolver,
        private PropertyNaming $propertyNaming,
        private UniqueObjectOrServiceDetector $uniqueObjectOrServiceDetector,
        private UniqueObjectFactoryFactory $uniqueObjectFactoryFactory,
        private FactoryClassPrinter $factoryClassPrinter,
        private PropertyToAddCollector $propertyToAddCollector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert new X/Static::call() to factories in entities, pass them via constructor to each other',
            [
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
    public function __construct(StaticClass $staticClass)
    {
        $this->staticClass = $staticClass;
    }

    public function someFun()
    {
        return $this->staticClass->staticMethod();
    }
}

final class AnotherClassFactory
{
    /**
     * @var StaticClass
     */
    private $staticClass;

    public function __construct(StaticClass $staticClass)
    {
        $this->staticClass = $staticClass;
    }

    public function create(): AnotherClass
    {
        return new AnotherClass($this->staticClass);
    }
}
CODE_SAMPLE
                    ,
                    [
                        self::TYPES_TO_SERVICES => ['StaticClass'],
                    ]
                ), ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, StaticCall::class];
    }

    /**
     * @param StaticCall|Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            return $this->refactorClass($node);
        }

        foreach ($this->serviceObjectTypes as $serviceObjectType) {
            if (! $this->isObjectType($node->class, $serviceObjectType)) {
                continue;
            }

            // is this object created via new somewhere else? use factory!
            $variableName = $this->propertyNaming->fqnToVariableName($serviceObjectType);
            $thisPropertyFetch = new PropertyFetch(new Variable('this'), $variableName);

            return new MethodCall($thisPropertyFetch, $node->name, $node->args);
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

    private function refactorClass(Class_ $class): Class_
    {
        $staticTypesInClass = $this->staticTypesInClassResolver->collectStaticCallTypeInClass(
            $class,
            $this->serviceObjectTypes
        );

        foreach ($staticTypesInClass as $staticTypeInClass) {
            $variableName = $this->propertyNaming->fqnToVariableName($staticTypeInClass);

            $propertyMetadata = new PropertyMetadata($variableName, $staticTypeInClass, Class_::MODIFIER_PRIVATE);
            $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);

            // is this an object? create factory for it next to this :)
            if ($this->uniqueObjectOrServiceDetector->isUniqueObject()) {
                $factoryClass = $this->uniqueObjectFactoryFactory->createFactoryClass($class, $staticTypeInClass);

                $this->factoryClassPrinter->printFactoryForClass($factoryClass, $class);
            }
        }

        return $class;
    }
}
