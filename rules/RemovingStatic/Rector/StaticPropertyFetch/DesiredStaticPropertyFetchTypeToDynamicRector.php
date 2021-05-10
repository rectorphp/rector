<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\StaticPropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\RemovingStatic\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\DesiredStaticPropertyFetchTypeToDynamicRectorTest
 */
final class DesiredStaticPropertyFetchTypeToDynamicRector extends AbstractRector
{
    /**
     * @var ObjectType[]
     */
    private $staticObjectTypes = [];

    public function __construct(
        private PropertyNaming $propertyNaming,
        ParameterProvider $parameterProvider
    ) {
        $typesToRemoveStaticFrom = $parameterProvider->provideArrayParameter(Option::TYPES_TO_REMOVE_STATIC_FROM);
        foreach ($typesToRemoveStaticFrom as $typeToRemoveStaticFrom) {
            $this->staticObjectTypes[] = new ObjectType($typeToRemoveStaticFrom);
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change defined static service to dynamic one', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        SomeStaticMethod::$someStatic;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $this->someStaticMethod::$someStatic;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticPropertyFetch::class];
    }

    /**
     * @param StaticPropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Scope $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $classObjectType = new ObjectType($classReflection->getName());

        // A. remove local fetch
        foreach ($this->staticObjectTypes as $staticObjectType) {
            if (! $staticObjectType->isSuperTypeOf($classObjectType)->yes()) {
                continue;
            }

            return new PropertyFetch(new Variable('this'), $node->name);
        }

        // B. external property fetch
        foreach ($this->staticObjectTypes as $staticObjectType) {
            if (! $this->isObjectType($node->class, $staticObjectType)) {
                continue;
            }

            $propertyName = $this->propertyNaming->fqnToVariableName($staticObjectType);

            /** @var Class_ $class */
            $class = $node->getAttribute(AttributeKey::CLASS_NODE);
            $this->addConstructorDependencyToClass($class, $staticObjectType, $propertyName);

            $objectPropertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
            return new PropertyFetch($objectPropertyFetch, $node->name);
        }

        return null;
    }
}
