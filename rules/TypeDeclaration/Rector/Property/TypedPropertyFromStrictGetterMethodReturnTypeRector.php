<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\GetterTypeDeclarationPropertyTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\TypedPropertyFromStrictGetterMethodReturnTypeRectorTest
 * @todo make generic
 */
final class TypedPropertyFromStrictGetterMethodReturnTypeRector extends AbstractRector
{
    public function __construct(
        private GetterTypeDeclarationPropertyTypeInferer $getterTypeDeclarationPropertyTypeInferer,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Complete property type based on getter strict types',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public $name;

    public function getName(): string|null
    {
        return $this->name;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public ?string $name;

    public function getName(): string|null
    {
        return $this->name;
    }
}
CODE_SAMPLE
                ), ]
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
    public function refactor(Node $node): ?Property
    {
        if ($node->type !== null) {
            return null;
        }

        if ($this->isGuardedByParentProperty($node)) {
            return null;
        }

        $getterReturnType = $this->getterTypeDeclarationPropertyTypeInferer->inferProperty($node);
        if (! $getterReturnType instanceof Type) {
            return null;
        }

        $propertyType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($getterReturnType, TypeKind::PROPERTY());
        if ($propertyType === null) {
            return null;
        }

        $node->type = $propertyType;
        $this->decorateDefaultNull($getterReturnType, $node);

        return $node;
    }

    private function decorateDefaultNull(Type $propertyType, Property $property): void
    {
        if (! TypeCombinator::containsNull($propertyType)) {
            return;
        }

        $propertyProperty = $property->props[0];
        if ($propertyProperty->default instanceof Expr) {
            return;
        }

        $propertyProperty->default = $this->nodeFactory->createNull();
    }

    private function isGuardedByParentProperty(Property $property): bool
    {
        $propertyName = $this->getName($property);

        $scope = $property->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasProperty($propertyName)) {
                return true;
            }
        }

        return false;
    }
}
