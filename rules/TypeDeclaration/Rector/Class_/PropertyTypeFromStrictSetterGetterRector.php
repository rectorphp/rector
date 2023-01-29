<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php74\Guard\MakePropertyTypedGuard;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\GetterTypeDeclarationPropertyTypeInferer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\SetterTypeDeclarationPropertyTypeInferer;
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
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\GetterTypeDeclarationPropertyTypeInferer
     */
    private $getterTypeDeclarationPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\SetterTypeDeclarationPropertyTypeInferer
     */
    private $setterTypeDeclarationPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\Php74\Guard\MakePropertyTypedGuard
     */
    private $makePropertyTypedGuard;
    public function __construct(GetterTypeDeclarationPropertyTypeInferer $getterTypeDeclarationPropertyTypeInferer, SetterTypeDeclarationPropertyTypeInferer $setterTypeDeclarationPropertyTypeInferer, MakePropertyTypedGuard $makePropertyTypedGuard)
    {
        $this->getterTypeDeclarationPropertyTypeInferer = $getterTypeDeclarationPropertyTypeInferer;
        $this->setterTypeDeclarationPropertyTypeInferer = $setterTypeDeclarationPropertyTypeInferer;
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
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
            if (!$this->isDefaultExprTypeCompatible($property, $getterSetterPropertyType)) {
                continue;
            }
            if (!$this->makePropertyTypedGuard->isLegal($property, \false)) {
                continue;
            }
            $propertyTypeDclaration = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($getterSetterPropertyType, TypeKind::PROPERTY);
            if (!$propertyTypeDclaration instanceof Node) {
                continue;
            }
            $property->type = $propertyTypeDclaration;
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
        if (!$getterBasedStrictType->equals($setterBasedStrictType)) {
            return null;
        }
        return $getterBasedStrictType;
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
}
