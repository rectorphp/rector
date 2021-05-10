<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeComparator;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\PHPStan\TypeHasher;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\TypeDeclaration\TypeNormalizer;
final class TypeComparator
{
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\TypeHasher
     */
    private $typeHasher;
    /**
     * @var \Rector\TypeDeclaration\TypeNormalizer
     */
    private $typeNormalizer;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\NodeTypeResolver\TypeComparator\ArrayTypeComparator
     */
    private $arrayTypeComparator;
    /**
     * @var \Rector\NodeTypeResolver\TypeComparator\ScalarTypeComparator
     */
    private $scalarTypeComparator;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\TypeHasher $typeHasher, \Rector\TypeDeclaration\TypeNormalizer $typeNormalizer, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\NodeTypeResolver\TypeComparator\ArrayTypeComparator $arrayTypeComparator, \Rector\NodeTypeResolver\TypeComparator\ScalarTypeComparator $scalarTypeComparator, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->typeHasher = $typeHasher;
        $this->typeNormalizer = $typeNormalizer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->arrayTypeComparator = $arrayTypeComparator;
        $this->scalarTypeComparator = $scalarTypeComparator;
        $this->typeFactory = $typeFactory;
    }
    public function areTypesEqual(\PHPStan\Type\Type $firstType, \PHPStan\Type\Type $secondType) : bool
    {
        if ($this->scalarTypeComparator->areEqualScalar($firstType, $secondType)) {
            return \true;
        }
        // aliases and types
        if ($this->areAliasedObjectMatchingFqnObject($firstType, $secondType)) {
            return \true;
        }
        if ($this->areArrayUnionConstantEqualTypes($firstType, $secondType)) {
            return \true;
        }
        $firstType = $this->typeNormalizer->normalizeArrayOfUnionToUnionArray($firstType);
        $secondType = $this->typeNormalizer->normalizeArrayOfUnionToUnionArray($secondType);
        if ($this->typeHasher->areTypesEqual($firstType, $secondType)) {
            return \true;
        }
        // is template of
        return $this->areArrayTypeWithSingleObjectChildToParent($firstType, $secondType);
    }
    public function arePhpParserAndPhpStanPhpDocTypesEqual(\PhpParser\Node $phpParserNode, \PHPStan\PhpDocParser\Ast\Type\TypeNode $phpStanDocTypeNode, \PhpParser\Node $node) : bool
    {
        $phpParserNodeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($phpParserNode);
        $phpStanDocType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($phpStanDocTypeNode, $node);
        // normalize bool union types
        $phpParserNodeType = $this->normalizeConstantBooleanType($phpParserNodeType);
        $phpStanDocType = $this->normalizeConstantBooleanType($phpStanDocType);
        return $this->areTypesEqual($phpParserNodeType, $phpStanDocType);
    }
    public function isSubtype(\PHPStan\Type\Type $checkedType, \PHPStan\Type\Type $mainType) : bool
    {
        if ($mainType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        if (!$mainType instanceof \PHPStan\Type\ArrayType) {
            return $mainType->isSuperTypeOf($checkedType)->yes();
        }
        if (!$checkedType instanceof \PHPStan\Type\ArrayType) {
            return $mainType->isSuperTypeOf($checkedType)->yes();
        }
        return $this->arrayTypeComparator->isSubtype($checkedType, $mainType);
    }
    private function areAliasedObjectMatchingFqnObject(\PHPStan\Type\Type $firstType, \PHPStan\Type\Type $secondType) : bool
    {
        if ($firstType instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType && $secondType instanceof \PHPStan\Type\ObjectType && $firstType->getFullyQualifiedClass() === $secondType->getClassName()) {
            return \true;
        }
        if (!$secondType instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
            return \false;
        }
        if (!$firstType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        return $secondType->getFullyQualifiedClass() === $firstType->getClassName();
    }
    /**
     * E.g. class A extends B, class B → A[] is subtype of B[] → keep A[]
     */
    private function areArrayTypeWithSingleObjectChildToParent(\PHPStan\Type\Type $firstType, \PHPStan\Type\Type $secondType) : bool
    {
        if (!$firstType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        if (!$secondType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        $firstArrayItemType = $firstType->getItemType();
        $secondArrayItemType = $secondType->getItemType();
        if ($this->isMutualObjectSubtypes($firstArrayItemType, $secondArrayItemType)) {
            return \true;
        }
        if (!$firstArrayItemType instanceof \PHPStan\Type\Generic\GenericClassStringType) {
            return \false;
        }
        if (!$secondArrayItemType instanceof \PHPStan\Type\Generic\GenericClassStringType) {
            return \false;
        }
        // @todo resolve later better with template map, @see https://github.com/symplify/symplify/pull/3034/commits/4f6be8b87e52117b1aa1613b9b689ae958a9d6f4
        return $firstArrayItemType->getGenericType() instanceof \PHPStan\Type\ObjectType && $secondArrayItemType->getGenericType() instanceof \PHPStan\Type\ObjectType;
    }
    private function isMutualObjectSubtypes(\PHPStan\Type\Type $firstArrayItemType, \PHPStan\Type\Type $secondArrayItemType) : bool
    {
        if ($firstArrayItemType instanceof \PHPStan\Type\ObjectType && $secondArrayItemType instanceof \PHPStan\Type\ObjectType) {
            if ($firstArrayItemType->isSuperTypeOf($secondArrayItemType)->yes()) {
                return \true;
            }
            if ($secondArrayItemType->isSuperTypeOf($firstArrayItemType)->yes()) {
                return \true;
            }
        }
        return \false;
    }
    private function normalizeSingleUnionType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if ($type instanceof \PHPStan\Type\UnionType) {
            $uniqueTypes = $this->typeFactory->uniquateTypes($type->getTypes());
            if (\count($uniqueTypes) === 1) {
                return $uniqueTypes[0];
            }
        }
        return $type;
    }
    private function areArrayUnionConstantEqualTypes(\PHPStan\Type\Type $firstType, \PHPStan\Type\Type $secondType) : bool
    {
        if (!$firstType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        if (!$secondType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        $firstKeyType = $this->normalizeSingleUnionType($firstType->getKeyType());
        $secondKeyType = $this->normalizeSingleUnionType($secondType->getKeyType());
        // mixed and integer type are mutual replaceable in practise
        if ($firstKeyType instanceof \PHPStan\Type\MixedType) {
            $firstKeyType = new \PHPStan\Type\IntegerType();
        }
        if ($secondKeyType instanceof \PHPStan\Type\MixedType) {
            $secondKeyType = new \PHPStan\Type\IntegerType();
        }
        if (!$this->areTypesEqual($firstKeyType, $secondKeyType)) {
            return \false;
        }
        $firstArrayType = $this->normalizeSingleUnionType($firstType->getItemType());
        $secondArrayType = $this->normalizeSingleUnionType($secondType->getItemType());
        return $this->areTypesEqual($firstArrayType, $secondArrayType);
    }
    private function normalizeConstantBooleanType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $type, callable $callable) : Type {
            if ($type instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
                return new \PHPStan\Type\BooleanType();
            }
            return $callable($type);
        });
    }
}
