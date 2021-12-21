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
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer;
final class VarDocPropertyTypeInferer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer
     */
    private $genericClassStringTypeNormalizer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer
     */
    private $defaultValuePropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer $defaultValuePropertyTypeInferer, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer $doctrineTypeAnalyzer, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->defaultValuePropertyTypeInferer = $defaultValuePropertyTypeInferer;
        $this->typeFactory = $typeFactory;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function inferProperty(\PhpParser\Node\Stmt\Property $property) : \PHPStan\Type\Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $resolvedType = $phpDocInfo->getVarType();
        if ($resolvedType instanceof \PHPStan\Type\VoidType) {
            return new \PHPStan\Type\MixedType();
        }
        // default value type must be added to each resolved type if set
        $propertyDefaultValue = $property->props[0]->default;
        if ($propertyDefaultValue instanceof \PhpParser\Node\Expr) {
            $resolvedType = $this->unionTypeWithDefaultExpr($property, $resolvedType);
        }
        return $this->genericClassStringTypeNormalizer->normalize($resolvedType);
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
