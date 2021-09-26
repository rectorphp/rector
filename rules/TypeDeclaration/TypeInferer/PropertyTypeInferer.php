<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\Sorter\TypeInfererSorter;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\VarDocPropertyTypeInferer;
final class PropertyTypeInferer
{
    /**
     * @var PropertyTypeInfererInterface[]
     */
    private $propertyTypeInferers = [];
    /**
     * @var \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer
     */
    private $genericClassStringTypeNormalizer;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer
     */
    private $defaultValuePropertyTypeInferer;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\VarDocPropertyTypeInferer
     */
    private $varDocPropertyTypeInferer;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @param PropertyTypeInfererInterface[] $propertyTypeInferers
     */
    public function __construct(\Rector\TypeDeclaration\Sorter\TypeInfererSorter $typeInfererSorter, \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer $defaultValuePropertyTypeInferer, \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\VarDocPropertyTypeInferer $varDocPropertyTypeInferer, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer $doctrineTypeAnalyzer, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, array $propertyTypeInferers)
    {
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->defaultValuePropertyTypeInferer = $defaultValuePropertyTypeInferer;
        $this->varDocPropertyTypeInferer = $varDocPropertyTypeInferer;
        $this->typeFactory = $typeFactory;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->propertyTypeInferers = $typeInfererSorter->sort($propertyTypeInferers);
    }
    public function inferProperty(\PhpParser\Node\Stmt\Property $property) : \PHPStan\Type\Type
    {
        $resolvedTypes = $this->getResolvedTypes($property);
        $resolvedTypes = $this->typeFactory->uniquateTypes($resolvedTypes);
        // if nothing is clear from variable use, we use @var doc as fallback
        if ($resolvedTypes !== []) {
            $resolvedType = $this->typeFactory->createMixedPassedOrUnionType($resolvedTypes);
        } else {
            // void type is not allowed in properties
            $resolvedType = $this->varDocPropertyTypeInferer->inferProperty($property);
            if ($resolvedType instanceof \PHPStan\Type\VoidType) {
                return new \PHPStan\Type\MixedType();
            }
        }
        // default value type must be added to each resolved type if set
        $propertyDefaultValue = $property->props[0]->default;
        if ($propertyDefaultValue instanceof \PhpParser\Node\Expr) {
            $resolvedType = $this->unionTypeWithDefaultExpr($property, $resolvedType);
        }
        return $this->genericClassStringTypeNormalizer->normalize($resolvedType);
    }
    /**
     * @return Type[]
     */
    private function getResolvedTypes(\PhpParser\Node\Stmt\Property $property) : array
    {
        $resolvedTypes = [];
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $hasByVarName = $phpDocInfo->hasByName('var');
        foreach ($this->propertyTypeInferers as $propertyTypeInferer) {
            $type = $propertyTypeInferer->inferProperty($property);
            if (!$type instanceof \PHPStan\Type\Type) {
                continue;
            }
            if ($type instanceof \PHPStan\Type\VoidType) {
                continue;
            }
            if ($property->type === null && $type instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType && $hasByVarName) {
                return [];
            }
            $resolvedTypes[] = $type;
        }
        return $resolvedTypes;
    }
    private function shouldUnionWithDefaultValue(\PHPStan\Type\Type $defaultValueType, \PHPStan\Type\Type $type) : bool
    {
        if ($defaultValueType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        // skip empty array type (mixed[])
        if ($defaultValueType instanceof \PHPStan\Type\ArrayType && $defaultValueType->getItemType() instanceof \PHPStan\Type\NeverType && !$type instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        if ($type instanceof \PHPStan\Type\MixedType) {
            return \true;
        }
        return !$this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type);
    }
    private function unionWithDefaultValueType(\PHPStan\Type\Type $defaultValueType, \PHPStan\Type\Type $resolvedType) : \PHPStan\Type\Type
    {
        $types = [];
        $types[] = $defaultValueType;
        if (!$resolvedType instanceof \PHPStan\Type\MixedType) {
            $types[] = $resolvedType;
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    private function unionTypeWithDefaultExpr(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $resolvedType) : \PHPStan\Type\Type
    {
        $defaultValueType = $this->defaultValuePropertyTypeInferer->inferProperty($property);
        if (!$defaultValueType instanceof \PHPStan\Type\Type) {
            return $resolvedType;
        }
        if (!$this->shouldUnionWithDefaultValue($defaultValueType, $resolvedType)) {
            return $resolvedType;
        }
        return $this->unionWithDefaultValueType($defaultValueType, $resolvedType);
    }
}
