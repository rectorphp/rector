<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypeComparator;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
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
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

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
        NodeTypeResolver $nodeTypeResolver,
        ArrayTypeComparator $arrayTypeComparator,
        \Rector\NodeTypeResolver\TypeComparator\ScalarTypeComparator $scalarTypeComparator
    ) {
        $this->typeHasher = $typeHasher;
        $this->typeNormalizer = $typeNormalizer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->nodeTypeResolver = $nodeTypeResolver;
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

        if (! $mainType instanceof ArrayType || ! $checkedType instanceof ArrayType) {
            return $mainType->isSuperTypeOf($checkedType)
                ->yes();
        }

//        if (! $checkedType instanceof ConstantArrayType) {
//            return $mainType->isSuperTypeOf($checkedType)
//                ->yes();
//        }

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

        if ($firstArrayItemType instanceof ObjectType && $secondArrayItemType instanceof ObjectType) {
            $firstFqnClassName = $this->nodeTypeResolver->getFullyQualifiedClassName($firstArrayItemType);
            $secondFqnClassName = $this->nodeTypeResolver->getFullyQualifiedClassName($secondArrayItemType);

            if (is_a($firstFqnClassName, $secondFqnClassName, true)) {
                return true;
            }

            if (is_a($secondFqnClassName, $firstFqnClassName, true)) {
                return true;
            }
        }

        return false;
    }
}
