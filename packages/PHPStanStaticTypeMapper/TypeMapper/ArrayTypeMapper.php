<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeMapper;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\ClassStringType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantIntegerType;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericClassStringType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\NeverType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower;
use RectorPrefix20220606\Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer;
use RectorPrefix20220606\Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use RectorPrefix20220606\Symfony\Contracts\Service\Attribute\Required;
/**
 * @see \Rector\Tests\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapperTest
 *
 * @implements TypeMapperInterface<ArrayType>
 */
final class ArrayTypeMapper implements TypeMapperInterface
{
    /**
     * @var string
     */
    public const HAS_GENERIC_TYPE_PARENT = 'has_generic_type_parent';
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    /**
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower
     */
    private $unionTypeCommonTypeNarrower;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer
     */
    private $genericClassStringTypeNormalizer;
    /**
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer
     */
    private $detailedTypeAnalyzer;
    /**
     * @var \Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayShapeTypeMapper
     */
    private $arrayShapeTypeMapper;
    // To avoid circular dependency
    /**
     * @required
     */
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper, UnionTypeCommonTypeNarrower $unionTypeCommonTypeNarrower, ReflectionProvider $reflectionProvider, GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, DetailedTypeAnalyzer $detailedTypeAnalyzer, ArrayShapeTypeMapper $arrayShapeTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->unionTypeCommonTypeNarrower = $unionTypeCommonTypeNarrower;
        $this->reflectionProvider = $reflectionProvider;
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->detailedTypeAnalyzer = $detailedTypeAnalyzer;
        $this->arrayShapeTypeMapper = $arrayShapeTypeMapper;
    }
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return ArrayType::class;
    }
    /**
     * @param TypeKind::* $typeKind
     * @param ArrayType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        $itemType = $type->getItemType();
        if ($itemType instanceof UnionType && !$type instanceof ConstantArrayType) {
            return $this->createArrayTypeNodeFromUnionType($itemType, $typeKind);
        }
        if ($type instanceof ConstantArrayType && $typeKind === TypeKind::RETURN) {
            $arrayShapeNode = $this->arrayShapeTypeMapper->mapConstantArrayType($type);
            if ($arrayShapeNode instanceof TypeNode) {
                return $arrayShapeNode;
            }
        }
        if ($itemType instanceof ArrayType && $this->isGenericArrayCandidate($itemType)) {
            return $this->createGenericArrayType($type, $typeKind, \true);
        }
        if ($this->isGenericArrayCandidate($type)) {
            return $this->createGenericArrayType($type, $typeKind, \true);
        }
        $narrowedTypeNode = $this->narrowConstantArrayTypeOfUnionType($type, $itemType, $typeKind);
        if ($narrowedTypeNode instanceof TypeNode) {
            return $narrowedTypeNode;
        }
        $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($itemType, $typeKind);
        return new SpacingAwareArrayTypeNode($itemTypeNode);
    }
    /**
     * @param ArrayType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        return new Name('array');
    }
    /**
     * @param TypeKind::* $typeKind
     */
    private function createArrayTypeNodeFromUnionType(UnionType $unionType, string $typeKind) : SpacingAwareArrayTypeNode
    {
        $unionedArrayType = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $typeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($unionedType, $typeKind);
            $unionedArrayType[(string) $typeNode] = $typeNode;
        }
        if (\count($unionedArrayType) > 1) {
            return new SpacingAwareArrayTypeNode(new BracketsAwareUnionTypeNode($unionedArrayType));
        }
        /** @var TypeNode $arrayType */
        $arrayType = \array_shift($unionedArrayType);
        return new SpacingAwareArrayTypeNode($arrayType);
    }
    private function isGenericArrayCandidate(ArrayType $arrayType) : bool
    {
        if ($arrayType->getKeyType() instanceof MixedType) {
            return \false;
        }
        if ($this->isClassStringArrayType($arrayType)) {
            return \true;
        }
        // skip simple arrays, like "string[]", from converting to obvious "array<int, string>"
        if ($this->isIntegerKeyAndNonNestedArray($arrayType)) {
            return \false;
        }
        if ($arrayType->getKeyType() instanceof NeverType) {
            return \false;
        }
        // make sure the integer key type is not natural/implicit array int keys
        $keysArrayType = $arrayType->getKeysArray();
        if (!$keysArrayType instanceof ConstantArrayType) {
            return \true;
        }
        foreach ($keysArrayType->getValueTypes() as $key => $keyType) {
            if (!$keyType instanceof ConstantIntegerType) {
                return \true;
            }
            if ($key !== $keyType->getValue()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param TypeKind::* $typeKind
     */
    private function createGenericArrayType(ArrayType $arrayType, string $typeKind, bool $withKey = \false) : GenericTypeNode
    {
        $itemType = $arrayType->getItemType();
        $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($itemType, $typeKind);
        $identifierTypeNode = new IdentifierTypeNode('array');
        // is class-string[] list only
        if ($this->isClassStringArrayType($arrayType)) {
            $withKey = \false;
        }
        if ($withKey) {
            $keyTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($arrayType->getKeyType(), $typeKind);
            if ($itemTypeNode instanceof BracketsAwareUnionTypeNode && $this->isPairClassTooDetailed($itemType)) {
                $genericTypes = [$keyTypeNode, $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode(new ClassStringType(), $typeKind)];
            } else {
                $genericTypes = [$keyTypeNode, $itemTypeNode];
            }
        } else {
            $genericTypes = [$itemTypeNode];
        }
        // @see https://github.com/phpstan/phpdoc-parser/blob/98a088b17966bdf6ee25c8a4b634df313d8aa531/tests/PHPStan/Parser/PhpDocParserTest.php#L2692-L2696
        foreach ($genericTypes as $genericType) {
            /** @var \PHPStan\PhpDocParser\Ast\Node $genericType */
            $genericType->setAttribute(self::HAS_GENERIC_TYPE_PARENT, $withKey);
        }
        $identifierTypeNode->setAttribute(self::HAS_GENERIC_TYPE_PARENT, $withKey);
        return new GenericTypeNode($identifierTypeNode, $genericTypes);
    }
    private function isPairClassTooDetailed(Type $itemType) : bool
    {
        if (!$itemType instanceof UnionType) {
            return \false;
        }
        if (!$this->genericClassStringTypeNormalizer->isAllGenericClassStringType($itemType)) {
            return \false;
        }
        return $this->detailedTypeAnalyzer->isTooDetailed($itemType);
    }
    private function isIntegerKeyAndNonNestedArray(ArrayType $arrayType) : bool
    {
        if (!$arrayType->getKeyType() instanceof IntegerType) {
            return \false;
        }
        return !$arrayType->getItemType() instanceof ArrayType;
    }
    /**
     * @param TypeKind::* $typeKind
     */
    private function narrowConstantArrayTypeOfUnionType(ArrayType $arrayType, Type $itemType, string $typeKind) : ?TypeNode
    {
        if ($arrayType instanceof ConstantArrayType && $itemType instanceof UnionType) {
            $narrowedItemType = $this->unionTypeCommonTypeNarrower->narrowToSharedObjectType($itemType);
            if ($narrowedItemType instanceof ObjectType) {
                $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($narrowedItemType, $typeKind);
                return new SpacingAwareArrayTypeNode($itemTypeNode);
            }
            $narrowedItemType = $this->unionTypeCommonTypeNarrower->narrowToGenericClassStringType($itemType);
            if ($narrowedItemType instanceof GenericClassStringType) {
                return $this->createTypeNodeFromGenericClassStringType($narrowedItemType, $typeKind);
            }
        }
        return null;
    }
    /**
     * @param TypeKind::* $typeKind
     * @return \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode|\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode
     */
    private function createTypeNodeFromGenericClassStringType(GenericClassStringType $genericClassStringType, string $typeKind)
    {
        $genericType = $genericClassStringType->getGenericType();
        if ($genericType instanceof ObjectType && !$this->reflectionProvider->hasClass($genericType->getClassName())) {
            return new IdentifierTypeNode($genericType->getClassName());
        }
        $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($genericClassStringType, $typeKind);
        return new GenericTypeNode(new IdentifierTypeNode('array'), [$itemTypeNode]);
    }
    private function isClassStringArrayType(ArrayType $arrayType) : bool
    {
        if ($arrayType->getKeyType() instanceof MixedType) {
            return $arrayType->getItemType() instanceof GenericClassStringType;
        }
        if ($arrayType->getKeyType() instanceof ConstantIntegerType) {
            return $arrayType->getItemType() instanceof GenericClassStringType;
        }
        return \false;
    }
}
