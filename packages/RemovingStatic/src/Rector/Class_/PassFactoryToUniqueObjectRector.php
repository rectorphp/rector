<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use Rector\Naming\PropertyNaming;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\RemovingStatic\Printer\FactoryClassPrinter;
use Rector\RemovingStatic\StaticTypesInClassResolver;
use Rector\RemovingStatic\UniqueObjectFactoryFactory;
use Rector\RemovingStatic\UniqueObjectOrServiceDetector;

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
        $this->typesToServices = $typesToServices;
        $this->propertyNaming = $propertyNaming;
        $this->uniqueObjectOrServiceDetector = $uniqueObjectOrServiceDetector;
        $this->uniqueObjectFactoryFactory = $uniqueObjectFactoryFactory;
        $this->factoryClassPrinter = $factoryClassPrinter;
        $this->staticTypesInClassResolver = $staticTypesInClassResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert new X/Static::call() to factories in entities, pass them via constructor to each other', [
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
                ['typesToServices' => ['StaticClass']]
            ), ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Node\Stmt\Class_::class, Node\Expr\StaticCall::class];
    }

    /**
     * @param Node\Expr\StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Node\Stmt\Class_) {
            $staticTypesInClass = $this->staticTypesInClassResolver->collectStaticCallTypeInClass(
                $node,
                $this->typesToServices
            );

            // nothing here
            if ($staticTypesInClass === []) {
                return null;
            }

            foreach ($staticTypesInClass as $staticTypeInClass) {
                $variableName = $this->propertyNaming->fqnToVariableName($staticTypeInClass);
                $this->addPropertyToClass($node, $staticTypeInClass, $variableName);
            }

            // is this an object? create factory for it next to this :)
            if ($this->uniqueObjectOrServiceDetector->isUniqueObject($node)) {
                $factoryClass = $this->uniqueObjectFactoryFactory->createFactoryClass($node, $staticTypesInClass);

                $this->factoryClassPrinter->printFactoryForClass($factoryClass, $node);
            }

            // @todo 3. iterate parents with same effect etc.
            return $node;
        }

        foreach ($this->typesToServices as $type) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            // is this object created via new somewhere else? use factory!
            $variableName = $this->propertyNaming->fqnToVariableName($type);
            $thisPropertyFetch = new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $variableName);

            return new Node\Expr\MethodCall($thisPropertyFetch, $node->name, $node->args);
        }

        return $node;
    }
}
