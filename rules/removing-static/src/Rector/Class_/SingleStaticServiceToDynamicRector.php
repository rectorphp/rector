<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\RemovingStatic\NodeAnalyzer\StaticCallPresenceAnalyzer;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\SingleStaticServiceToDynamicRectorTest
 */
final class SingleStaticServiceToDynamicRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_TYPES = 'class_types';

    /**
     * @var string
     */
    private const THIS = 'this';

    /**
     * @var string[]
     */
    private $classTypes = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var StaticCallPresenceAnalyzer
     */
    private $staticCallPresenceAnalyzer;

    public function __construct(PropertyNaming $propertyNaming, StaticCallPresenceAnalyzer $staticCallPresenceAnalyzer)
    {
        $this->propertyNaming = $propertyNaming;
        $this->staticCallPresenceAnalyzer = $staticCallPresenceAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change full static service, to dynamic one', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class AnotherClass
{
    public function run()
    {
        SomeClass::someStatic();
    }
}

class SomeClass
{
    public static function run()
    {
        self::someStatic();
    }

    private static function someStatic()
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class AnotherClass
{
    /**
     * @var SomeClass
     */
    private $someClass;

    public fuction __construct(SomeClass $someClass)
    {
        $this->someClass = $someClass;
    }

    public function run()
    {
        SomeClass::someStatic();
    }
}

class SomeClass
{
    public function run()
    {
        $this->someStatic();
    }

    private function someStatic()
    {
    }
}
CODE_SAMPLE
,
                [
                    self::CLASS_TYPES => ['SomeClass'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, ClassMethod::class, StaticCall::class, Property::class, StaticPropertyFetch::class];
    }

    /**
     * @param Class_|ClassMethod|StaticCall|Property|StaticPropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }

        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }

        if ($node instanceof Class_) {
            return $this->refactorClass($node);
        }

        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }

        if ($node instanceof StaticPropertyFetch) {
            return $this->refactorStaticPropertyFetch($node);
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->classTypes = $configuration[self::CLASS_TYPES] ?? [];
    }

    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        foreach ($this->classTypes as $classType) {
            if (! $this->isInClassNamed($classMethod, $classType)) {
                continue;
            }

            $this->makeNonStatic($classMethod);
            return $classMethod;
        }

        return null;
    }

    private function refactorStaticCall(StaticCall $staticCall): ?MethodCall
    {
        foreach ($this->classTypes as $classType) {
            if (! $this->isObjectType($staticCall->class, $classType)) {
                continue;
            }

            // is the same class or external call?
            $className = $this->getName($staticCall->class);
            if ($className === 'self') {
                return new MethodCall(new Variable(self::THIS), $staticCall->name, $staticCall->args);
            }

            $propertyName = $this->propertyNaming->fqnToVariableName($classType);

            $currentMethodName = $staticCall->getAttribute(AttributeKey::METHOD_NAME);
            if ($currentMethodName === MethodName::CONSTRUCT) {
                $propertyFetch = new Variable($propertyName);
            } else {
                $propertyFetch = new PropertyFetch(new Variable(self::THIS), $propertyName);
            }
            return new MethodCall($propertyFetch, $staticCall->name, $staticCall->args);
        }

        return null;
    }

    private function refactorClass(Class_ $class): ?Class_
    {
        foreach ($this->classTypes as $classType) {
            // do not any dependencies to class itself
            if ($this->isObjectType($class, $classType)) {
                continue;
            }

            $this->completeDependencyToConstructorOnly($class, $classType);

            if ($this->staticCallPresenceAnalyzer->hasClassAnyMethodWithStaticCallOnType($class, $classType)) {
                $singleObjectType = new ObjectType($classType);
                $propertyExpectedName = $this->propertyNaming->fqnToVariableName($classType);
                $this->addConstructorDependencyToClass($class, $singleObjectType, $propertyExpectedName);

                return $class;
            }
        }

        return null;
    }

    private function refactorProperty(Property $property): ?Property
    {
        if (! $property->isStatic()) {
            return null;
        }

        foreach ($this->classTypes as $classType) {
            if (! $this->isInClassNamed($property, $classType)) {
                continue;
            }

            $this->makeNonStatic($property);
            return $property;
        }

        return null;
    }

    private function refactorStaticPropertyFetch(StaticPropertyFetch $staticPropertyFetch): ?PropertyFetch
    {
        // A. remove local fetch
        foreach ($this->classTypes as $classType) {
            if (! $this->isInClassNamed($staticPropertyFetch, $classType)) {
                continue;
            }

            return new PropertyFetch(new Variable(self::THIS), $staticPropertyFetch->name);
        }

        // B. external property fetch
        // A. remove local fetch
        foreach ($this->classTypes as $classType) {
            if (! $this->isObjectType($staticPropertyFetch->class, $classType)) {
                continue;
            }

            $propertyName = $this->propertyNaming->fqnToVariableName($classType);

            /** @var Class_ $class */
            $class = $staticPropertyFetch->getAttribute(AttributeKey::CLASS_NODE);
            $this->addConstructorDependencyToClass($class, new ObjectType($classType), $propertyName);

            $objectPropertyFetch = new PropertyFetch(new Variable(self::THIS), $propertyName);
            return new PropertyFetch($objectPropertyFetch, $staticPropertyFetch->name);
        }

        return null;
    }

    private function completeDependencyToConstructorOnly(Class_ $class, string $classType): void
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod === null) {
            return;
        }

        $hasStaticCall = $this->staticCallPresenceAnalyzer->hasMethodStaticCallOnType(
            $constructClassMethod,
            $classType
        );

        if (! $hasStaticCall) {
            return;
        }

        $propertyExpectedName = $this->propertyNaming->fqnToVariableName(new ObjectType($classType));

        if ($this->isTypeAlreadyInParamMethod($constructClassMethod, $classType)) {
            return;
        }

        $constructClassMethod->params[] = new Param(
            new Variable($propertyExpectedName),
            null,
            new FullyQualified($classType)
        );
    }

    private function isTypeAlreadyInParamMethod(ClassMethod $classMethod, string $classType): bool
    {
        foreach ($classMethod->getParams() as $param) {
            if ($param->type === null) {
                continue;
            }

            if ($this->isName($param->type, $classType)) {
                return true;
            }
        }

        return false;
    }
}
