<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\RestoreDefaultNullToNullableTypePropertyRectorTest
 */
final class RestoreDefaultNullToNullableTypePropertyRector extends AbstractRector implements MinPhpVersionInterface
{
    public function __construct(private readonly ConstructorAssignDetector $constructorAssignDetector)
    {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add null default to properties with PHP 7.4 property nullable type',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public ?string $name;
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public ?string $name = null;
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $onlyProperty = $node->props[0];
        $onlyProperty->default = $this->nodeFactory->createNull();

        return $node;
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }

    private function shouldSkip(Property $property): bool
    {
        if (! $property->type instanceof NullableType) {
            return true;
        }

        if (count($property->props) > 1) {
            return true;
        }

        $onlyProperty = $property->props[0];
        if ($onlyProperty->default !== null) {
            return true;
        }

        if ($property->isReadonly()) {
            return true;
        }

        // is variable assigned in constructor
        $propertyName = $this->getName($property);
        $classLike = $this->betterNodeFinder->findParentType($property, Class_::class);

        // a trait can be used in multiple context, we don't know whether it is assigned in __construct or not
        // so it needs to has null default
        if (! $classLike instanceof Class_) {
            return false;
        }

        return $this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName);
    }
}
