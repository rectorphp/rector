<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypeComparator;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\TypeHasher;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\TypeDeclaration\TypeNormalizer;

final class TypeComparator
{
    /**
     * @var TypeHasher
     */
    private $typeHasher;

    /**
     * @var TypeNormalizer
     */
    private $typeNormalizer;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var ArrayTypeComparator
     */
    private $arrayTypeComparator;

    /**
     * @var ScalarTypeComparator
     */
    private $scalarTypeComparator;

    public function __construct(
        TypeHasher $typeHasher,
        TypeNormalizer $typeNormalizer,
        StaticTypeMapper $staticTypeMapper,
        ArrayTypeComparator $arrayTypeComparator,
        ScalarTypeComparator $scalarTypeComparator
    ) {
        $this->typeHasher = $typeHasher;
        $this->typeNormalizer = $typeNormalizer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->arrayTypeComparator = $arrayTypeComparator;
        $this->scalarTypeComparator = $scalarTypeComparator;
    }

    public function areTypesEqual(Type $firstType, Type $secondType): bool
    {
        if ($this->scalarTypeComparator->areEqualScalar($firstType, $secondType)) {
            return true;
        }

        // aliases and types
        if ($this->areAliasedObjectMatchingFqnObject($firstType, $secondType)) {
            return true;
        }

        $firstType = $this->typeNormalizer->normalizeArrayOfUnionToUnionArray($firstType);
        $secondType = $this->typeNormalizer->normalizeArrayOfUnionToUnionArray($secondType);

        if ($this->typeHasher->areTypesEqual($firstType, $secondType)) {
            return true;
        }

        // is template of
        return $this->areArrayTypeWithSingleObjectChildToParent($firstType, $secondType);
    }

    public function arePhpParserAndPhpStanPhpDocTypesEqual(
        Node $phpParserNode,
        TypeNode $phpStanDocTypeNode,
        Node $node
    ): bool {
        $phpParserNodeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($phpParserNode);
        $phpStanDocType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $phpStanDocTypeNode,
            $node
        );

        return $this->areTypesEqual($phpParserNodeType, $phpStanDocType);
    }

    public function isSubtype(Type $checkedType, Type $mainType): bool
    {
        if ($mainType instanceof MixedType) {
            return false;
        }

        if (! $mainType instanceof ArrayType) {
            return $mainType->isSuperTypeOf($checkedType)
                ->yes();
        }

        if (! $checkedType instanceof ArrayType) {
            return $mainType->isSuperTypeOf($checkedType)
                ->yes();
        }

        return $this->arrayTypeComparator->isSubtype($checkedType, $mainType);
    }

    private function areAliasedObjectMatchingFqnObject(Type $firstType, Type $secondType): bool
    {
        if ($firstType instanceof AliasedObjectType && $secondType instanceof ObjectType && $firstType->getFullyQualifiedClass() === $secondType->getClassName()) {
            return true;
        }
        if (! $secondType instanceof AliasedObjectType) {
            return false;
        }
        if (! $firstType instanceof ObjectType) {
            return false;
        }
        return $secondType->getFullyQualifiedClass() === $firstType->getClassName();
    }

    /**
     * E.g. class A extends B, class B → A[] is subtype of B[] → keep A[]
     */
    private function areArrayTypeWithSingleObjectChildToParent(Type $firstType, Type $secondType): bool
    {
        if (! $firstType instanceof ArrayType) {
            return false;
        }
        if (! $secondType instanceof ArrayType) {
            return false;
        }

        $firstArrayItemType = $firstType->getItemType();
        $secondArrayItemType = $secondType->getItemType();

        if ($this->isMutualObjectSubtypes($firstArrayItemType, $secondArrayItemType)) {
            return true;
        }

        if (! $firstArrayItemType instanceof GenericClassStringType) {
            return false;
        }

        if (! $secondArrayItemType instanceof GenericClassStringType) {
            return false;
        }

        // @todo resolve later better with template map, @see https://github.com/symplify/symplify/pull/3034/commits/4f6be8b87e52117b1aa1613b9b689ae958a9d6f4
        return $firstArrayItemType->getGenericType() instanceof ObjectType && $secondArrayItemType->getGenericType() instanceof ObjectType;
    }

    private function isMutualObjectSubtypes(Type $firstArrayItemType, Type $secondArrayItemType): bool
    {
        if ($firstArrayItemType instanceof ObjectType && $secondArrayItemType instanceof ObjectType) {
            if ($firstArrayItemType->isSuperTypeOf($secondArrayItemType)->yes()) {
                return true;
            }

            if ($secondArrayItemType->isSuperTypeOf($firstArrayItemType)->yes()) {
                return true;
            }
        }

        return false;
    }
}
