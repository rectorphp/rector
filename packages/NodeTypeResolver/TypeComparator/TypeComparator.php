<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\TypeComparator;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantBooleanType;
use RectorPrefix20220606\PHPStan\Type\ConstantScalarType;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericClassStringType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\StaticType;
use RectorPrefix20220606\PHPStan\Type\ThisType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\TypeTraverser;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use RectorPrefix20220606\Rector\NodeTypeResolver\PHPStan\TypeHasher;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use RectorPrefix20220606\Rector\TypeDeclaration\TypeNormalizer;
final class TypeComparator
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\TypeHasher
     */
    private $typeHasher;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeNormalizer
     */
    private $typeNormalizer;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\ArrayTypeComparator
     */
    private $arrayTypeComparator;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\ScalarTypeComparator
     */
    private $scalarTypeComparator;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(TypeHasher $typeHasher, TypeNormalizer $typeNormalizer, StaticTypeMapper $staticTypeMapper, ArrayTypeComparator $arrayTypeComparator, ScalarTypeComparator $scalarTypeComparator, TypeFactory $typeFactory, UnionTypeAnalyzer $unionTypeAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->typeHasher = $typeHasher;
        $this->typeNormalizer = $typeNormalizer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->arrayTypeComparator = $arrayTypeComparator;
        $this->scalarTypeComparator = $scalarTypeComparator;
        $this->typeFactory = $typeFactory;
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function areTypesEqual(Type $firstType, Type $secondType) : bool
    {
        $firstTypeHash = $this->typeHasher->createTypeHash($firstType);
        $secondTypeHash = $this->typeHasher->createTypeHash($secondType);
        if ($firstTypeHash === $secondTypeHash) {
            return \true;
        }
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
    public function arePhpParserAndPhpStanPhpDocTypesEqual(Node $phpParserNode, TypeNode $phpStanDocTypeNode, Node $node) : bool
    {
        $phpParserNodeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($phpParserNode);
        $phpStanDocType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($phpStanDocTypeNode, $node);
        // normalize bool union types
        $phpParserNodeType = $this->normalizeConstantBooleanType($phpParserNodeType);
        $phpStanDocType = $this->normalizeConstantBooleanType($phpStanDocType);
        // is scalar replace by another - remove it?
        $areDifferentScalarTypes = $this->scalarTypeComparator->areDifferentScalarTypes($phpParserNodeType, $phpStanDocType);
        if (!$areDifferentScalarTypes && !$this->areTypesEqual($phpParserNodeType, $phpStanDocType)) {
            return \false;
        }
        if ($this->isTypeSelfAndDocParamTypeStatic($phpStanDocType, $phpParserNodeType, $phpStanDocTypeNode)) {
            return \false;
        }
        if ($this->areTypesSameWithLiteralTypeInPhpDoc($areDifferentScalarTypes, $phpStanDocType, $phpParserNodeType)) {
            return \false;
        }
        return $this->isThisTypeInFinalClass($phpStanDocType, $phpParserNodeType, $phpParserNode);
    }
    public function isSubtype(Type $checkedType, Type $mainType) : bool
    {
        if ($mainType instanceof MixedType) {
            return \false;
        }
        if (!$mainType instanceof ArrayType) {
            return $mainType->isSuperTypeOf($checkedType)->yes();
        }
        if (!$checkedType instanceof ArrayType) {
            return $mainType->isSuperTypeOf($checkedType)->yes();
        }
        return $this->arrayTypeComparator->isSubtype($checkedType, $mainType);
    }
    public function areTypesPossiblyIncluded(?Type $assumptionType, ?Type $exactType) : bool
    {
        if (!$assumptionType instanceof Type) {
            return \true;
        }
        if (!$exactType instanceof Type) {
            return \true;
        }
        if ($this->areTypesEqual($assumptionType, $exactType)) {
            return \true;
        }
        if (!$assumptionType instanceof UnionType) {
            return \true;
        }
        if (!$exactType instanceof UnionType) {
            return \true;
        }
        $countAssumpionTypeTypes = \count($assumptionType->getTypes());
        $countExactTypeTypes = \count($exactType->getTypes());
        if ($countAssumpionTypeTypes === $countExactTypeTypes) {
            $unionType = $this->unionTypeAnalyzer->mapGenericToClassStringType($exactType);
            return $this->areTypesEqual($assumptionType, $unionType);
        }
        return $countAssumpionTypeTypes > $countExactTypeTypes;
    }
    private function areAliasedObjectMatchingFqnObject(Type $firstType, Type $secondType) : bool
    {
        if ($firstType instanceof AliasedObjectType && $secondType instanceof ObjectType) {
            return $firstType->getFullyQualifiedName() === $secondType->getClassName();
        }
        if (!$firstType instanceof ObjectType) {
            return \false;
        }
        if (!$secondType instanceof AliasedObjectType) {
            return \false;
        }
        return $secondType->getFullyQualifiedName() === $firstType->getClassName();
    }
    /**
     * E.g. class A extends B, class B → A[] is subtype of B[] → keep A[]
     */
    private function areArrayTypeWithSingleObjectChildToParent(Type $firstType, Type $secondType) : bool
    {
        if (!$firstType instanceof ArrayType) {
            return \false;
        }
        if (!$secondType instanceof ArrayType) {
            return \false;
        }
        $firstArrayItemType = $firstType->getItemType();
        $secondArrayItemType = $secondType->getItemType();
        if ($this->isMutualObjectSubtypes($firstArrayItemType, $secondArrayItemType)) {
            return \true;
        }
        if (!$firstArrayItemType instanceof GenericClassStringType) {
            return \false;
        }
        if (!$secondArrayItemType instanceof GenericClassStringType) {
            return \false;
        }
        // @todo resolve later better with template map, @see https://github.com/symplify/symplify/pull/3034/commits/4f6be8b87e52117b1aa1613b9b689ae958a9d6f4
        return $firstArrayItemType->getGenericType() instanceof ObjectType && $secondArrayItemType->getGenericType() instanceof ObjectType;
    }
    private function isMutualObjectSubtypes(Type $firstArrayItemType, Type $secondArrayItemType) : bool
    {
        if (!$firstArrayItemType instanceof ObjectType) {
            return \false;
        }
        if (!$secondArrayItemType instanceof ObjectType) {
            return \false;
        }
        if ($firstArrayItemType->isSuperTypeOf($secondArrayItemType)->yes()) {
            return \true;
        }
        return $secondArrayItemType->isSuperTypeOf($firstArrayItemType)->yes();
    }
    private function normalizeSingleUnionType(Type $type) : Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        $uniqueTypes = $this->typeFactory->uniquateTypes($type->getTypes());
        if (\count($uniqueTypes) !== 1) {
            return $type;
        }
        return $uniqueTypes[0];
    }
    private function areArrayUnionConstantEqualTypes(Type $firstType, Type $secondType) : bool
    {
        if (!$firstType instanceof ArrayType) {
            return \false;
        }
        if (!$secondType instanceof ArrayType) {
            return \false;
        }
        if ($firstType instanceof ConstantArrayType || $secondType instanceof ConstantArrayType) {
            return \false;
        }
        $firstKeyType = $this->normalizeSingleUnionType($firstType->getKeyType());
        $secondKeyType = $this->normalizeSingleUnionType($secondType->getKeyType());
        // mixed and integer type are mutual replaceable in practise
        if ($firstKeyType instanceof MixedType) {
            $firstKeyType = new IntegerType();
        }
        if ($secondKeyType instanceof MixedType) {
            $secondKeyType = new IntegerType();
        }
        if (!$this->areTypesEqual($firstKeyType, $secondKeyType)) {
            return \false;
        }
        $firstArrayType = $this->normalizeSingleUnionType($firstType->getItemType());
        $secondArrayType = $this->normalizeSingleUnionType($secondType->getItemType());
        return $this->areTypesEqual($firstArrayType, $secondArrayType);
    }
    private function normalizeConstantBooleanType(Type $type) : Type
    {
        return TypeTraverser::map($type, function (Type $type, callable $callable) : Type {
            if ($type instanceof ConstantBooleanType) {
                return new BooleanType();
            }
            return $callable($type);
        });
    }
    private function isTypeSelfAndDocParamTypeStatic(Type $phpStanDocType, Type $phpParserNodeType, TypeNode $phpStanDocTypeNode) : bool
    {
        return $phpStanDocType instanceof StaticType && $phpParserNodeType instanceof ThisType && $phpStanDocTypeNode->getAttribute(PhpDocAttributeKey::PARENT) instanceof ParamTagValueNode;
    }
    private function areTypesSameWithLiteralTypeInPhpDoc(bool $areDifferentScalarTypes, Type $phpStanDocType, Type $phpParserNodeType) : bool
    {
        return $areDifferentScalarTypes && $phpStanDocType instanceof ConstantScalarType && $phpParserNodeType->isSuperTypeOf($phpStanDocType)->yes();
    }
    private function isThisTypeInFinalClass(Type $phpStanDocType, Type $phpParserNodeType, Node $node) : bool
    {
        /**
         * Special case for $this/(self|static) compare
         *
         * $this refers to the exact object identity, not just the same type. Therefore, it's valid and should not be removed
         * @see https://wiki.php.net/rfc/this_return_type for more context
         */
        if ($phpStanDocType instanceof ThisType && $phpParserNodeType instanceof StaticType) {
            return \false;
        }
        $isStaticReturnDocTypeWithThisType = $phpStanDocType instanceof StaticType && $phpParserNodeType instanceof ThisType;
        if (!$isStaticReturnDocTypeWithThisType) {
            return \true;
        }
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return \false;
        }
        return $class->isFinal();
    }
}
