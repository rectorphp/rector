<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\Naming\PropertyNaming;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\RemovingStatic\Printer\FactoryClassPrinter;
use Rector\RemovingStatic\StaticTypesInClassResolver;
use Rector\RemovingStatic\UniqueObjectFactoryFactory;
use Rector\RemovingStatic\UniqueObjectOrServiceDetector;

/**
 * @see \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\PassFactoryToEntityRectorTest
 */
final class PassFactoryToUniqueObjectRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $typesToServices = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var UniqueObjectOrServiceDetector
     */
    private $uniqueObjectOrServiceDetector;

    /**
     * @var UniqueObjectFactoryFactory
     */
    private $uniqueObjectFactoryFactory;

    /**
     * @var FactoryClassPrinter
     */
    private $factoryClassPrinter;

    /**
     * @var StaticTypesInClassResolver
     */
    private $staticTypesInClassResolver;

    /**
     * @param string[] $typesToServices
     */
    public function __construct(
        StaticTypesInClassResolver $staticTypesInClassResolver,
        PropertyNaming $propertyNaming,
        UniqueObjectOrServiceDetector $uniqueObjectOrServiceDetector,
        UniqueObjectFactoryFactory $uniqueObjectFactoryFactory,
        FactoryClassPrinter $factoryClassPrinter,
        array $typesToServices = []
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->uniqueObjectOrServiceDetector = $uniqueObjectOrServiceDetector;
        $this->uniqueObjectFactoryFactory = $uniqueObjectFactoryFactory;
        $this->factoryClassPrinter = $factoryClassPrinter;
        $this->staticTypesInClassResolver = $staticTypesInClassResolver;
        $this->typesToServices = $typesToServices;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert new X/Static::call() to factories in entities, pass them via constructor to each other', [
            new ConfiguredCodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
                ,
                [
                    'typesToServices' => ['StaticClass'],
                ]
            ), ]);
    }

    /**
     * @return string[]
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

        foreach ($this->typesToServices as $type) {
            if (! $this->isObjectType($node->class, $type)) {
                continue;
            }

            $objectType = new FullyQualifiedObjectType($type);

            // is this object created via new somewhere else? use factory!
            $variableName = $this->propertyNaming->fqnToVariableName($objectType);
            $thisPropertyFetch = new PropertyFetch(new Variable('this'), $variableName);

            return new MethodCall($thisPropertyFetch, $node->name, $node->args);
        }

        return $node;
    }

    private function refactorClass(Class_ $class): Class_
    {
        $staticTypesInClass = $this->staticTypesInClassResolver->collectStaticCallTypeInClass(
            $class,
            $this->typesToServices
        );

        foreach ($staticTypesInClass as $staticType) {
            $variableName = $this->propertyNaming->fqnToVariableName($staticType);
            $this->addPropertyToClass($class, $staticType, $variableName);

            // is this an object? create factory for it next to this :)
            if ($this->uniqueObjectOrServiceDetector->isUniqueObject()) {
                $factoryClass = $this->uniqueObjectFactoryFactory->createFactoryClass($class, $staticType);

                $this->factoryClassPrinter->printFactoryForClass($factoryClass, $class);
            }
        }

        // @todo 3. iterate parents with same effect etc.
        return $class;
    }
}
