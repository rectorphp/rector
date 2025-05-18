<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\Php74\Guard\MakePropertyTypedGuard;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\GetterTypeDeclarationPropertyTypeInferer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\SetterTypeDeclarationPropertyTypeInferer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\PropertyTypeFromStrictSetterGetterRector\PropertyTypeFromStrictSetterGetterRectorTest
 */
final class PropertyTypeFromStrictSetterGetterRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private GetterTypeDeclarationPropertyTypeInferer $getterTypeDeclarationPropertyTypeInferer;
    /**
     * @readonly
     */
    private SetterTypeDeclarationPropertyTypeInferer $setterTypeDeclarationPropertyTypeInferer;
    /**
     * @readonly
     */
    private MakePropertyTypedGuard $makePropertyTypedGuard;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(GetterTypeDeclarationPropertyTypeInferer $getterTypeDeclarationPropertyTypeInferer, SetterTypeDeclarationPropertyTypeInferer $setterTypeDeclarationPropertyTypeInferer, MakePropertyTypedGuard $makePropertyTypedGuard, ReflectionResolver $reflectionResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->getterTypeDeclarationPropertyTypeInferer = $getterTypeDeclarationPropertyTypeInferer;
        $this->setterTypeDeclarationPropertyTypeInferer = $setterTypeDeclarationPropertyTypeInferer;
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
        $this->reflectionResolver = $reflectionResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add property type based on strict setter and getter method', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $name = 'John';

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private string $name = 'John';

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        $classReflection = null;
        foreach ($node->getProperties() as $property) {
            if ($property->type instanceof Node) {
                continue;
            }
            if (!$property->isPrivate()) {
                continue;
            }
            $getterSetterPropertyType = $this->matchGetterSetterIdenticalType($property, $node);
            if (!$getterSetterPropertyType instanceof Type) {
                continue;
            }
            $hasPropertyDefaultNull = $this->hasPropertyDefaultNull($property);
            if (!$hasPropertyDefaultNull && !$this->isDefaultExprTypeCompatible($property, $getterSetterPropertyType)) {
                continue;
            }
            if (!$classReflection instanceof ClassReflection) {
                $classReflection = $this->reflectionResolver->resolveClassReflection($node);
            }
            if (!$classReflection instanceof ClassReflection) {
                return null;
            }
            if (!$this->makePropertyTypedGuard->isLegal($property, $classReflection, \false)) {
                continue;
            }
            $propertyTypeDeclaration = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($getterSetterPropertyType, TypeKind::PROPERTY);
            if (!$propertyTypeDeclaration instanceof Node) {
                continue;
            }
            $this->decorateDefaultExpr($getterSetterPropertyType, $property, $hasPropertyDefaultNull);
            $property->type = $propertyTypeDeclaration;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function matchGetterSetterIdenticalType(Property $property, Class_ $class) : ?Type
    {
        $getterBasedStrictType = $this->getterTypeDeclarationPropertyTypeInferer->inferProperty($property, $class);
        if (!$getterBasedStrictType instanceof Type) {
            return null;
        }
        $setterBasedStrictType = $this->setterTypeDeclarationPropertyTypeInferer->inferProperty($property, $class);
        if (!$setterBasedStrictType instanceof Type) {
            return null;
        }
        // single type
        if ($setterBasedStrictType->equals($getterBasedStrictType)) {
            return $setterBasedStrictType;
        }
        if ($getterBasedStrictType instanceof UnionType) {
            $getterBasedStrictTypes = $getterBasedStrictType->getTypes();
        } else {
            $getterBasedStrictTypes = [$getterBasedStrictType];
        }
        if ($setterBasedStrictType instanceof UnionType) {
            $setterBasedStrictTypes = $setterBasedStrictType->getTypes();
        } else {
            $setterBasedStrictTypes = [$setterBasedStrictType];
        }
        return new UnionType(\array_merge($setterBasedStrictTypes, $getterBasedStrictTypes));
    }
    private function isDefaultExprTypeCompatible(Property $property, Type $getterSetterPropertyType) : bool
    {
        $defaultExpr = $property->props[0]->default ?? null;
        // make sure default value is not a conflicting type
        if (!$defaultExpr instanceof Node) {
            // no value = no problem :)
            return \true;
        }
        $defaultExprType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($defaultExpr);
        return $defaultExprType->equals($getterSetterPropertyType);
    }
    private function decorateDefaultExpr(Type $getterSetterPropertyType, Property $property, bool $hasPropertyDefaultNull) : void
    {
        if (!TypeCombinator::containsNull($getterSetterPropertyType)) {
            if ($hasPropertyDefaultNull) {
                // reset to nothing
                $property->props[0]->default = null;
            }
            return;
        }
        $propertyProperty = $property->props[0];
        // already set → skip it
        if ($propertyProperty->default instanceof Expr) {
            return;
        }
        $propertyProperty->default = new ConstFetch(new Name('null'));
    }
    private function hasPropertyDefaultNull(Property $property) : bool
    {
        $defaultExpr = $property->props[0]->default ?? null;
        if (!$defaultExpr instanceof ConstFetch) {
            return \false;
        }
        return $defaultExpr->name->toLowerString() === 'null';
    }
}
