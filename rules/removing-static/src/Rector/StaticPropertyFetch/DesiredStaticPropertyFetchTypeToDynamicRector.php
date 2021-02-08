<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\StaticPropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\RemovingStatic\Tests\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\DesiredStaticPropertyFetchTypeToDynamicRectorTest
 */
final class DesiredStaticPropertyFetchTypeToDynamicRector extends AbstractRector
{
    /**
     * @var class-string[]
     */
    private $classTypes = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming, ParameterProvider $parameterProvider)
    {
        $this->classTypes = $parameterProvider->provideArrayParameter(Option::TYPES_TO_REMOVE_STATIC_FROM);

        $this->propertyNaming = $propertyNaming;
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
     * @return string[]
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
        // A. remove local fetch
        foreach ($this->classTypes as $classType) {
            if (! $this->nodeNameResolver->isInClassNamed($node, $classType)) {
                continue;
            }

            return new PropertyFetch(new Variable('this'), $node->name);
        }

        // B. external property fetch
        foreach ($this->classTypes as $classType) {
            if (! $this->isObjectType($node->class, $classType)) {
                continue;
            }

            $propertyName = $this->propertyNaming->fqnToVariableName($classType);

            /** @var Class_ $class */
            $class = $node->getAttribute(AttributeKey::CLASS_NODE);
            $this->addConstructorDependencyToClass($class, new ObjectType($classType), $propertyName);

            $objectPropertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
            return new PropertyFetch($objectPropertyFetch, $node->name);
        }

        return null;
    }
}
