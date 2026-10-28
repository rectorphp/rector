<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
/**
 * @see \Rector\Tests\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapperTest
 *
 * @implements TypeMapperInterface<ArrayType>
 */
final class ArrayTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     */
    private GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer;
    /**
     * @readonly
     */
    private DetailedTypeAnalyzer $detailedTypeAnalyzer;
    private PHPStanStaticTypeMapper $phpStanStaticTypeMapper;
    public function __construct(GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, DetailedTypeAnalyzer $detailedTypeAnalyzer)
    {
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->detailedTypeAnalyzer = $detailedTypeAnalyzer;
    }
    // To avoid circular dependency
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    /**
     * @return array<class-string<Type>>
     */
    public function getNodeClasses(): array
    {
        return [ArrayType::class];
    }
    /**
     * @param ArrayType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $this->splitUnionItemType($this->createArrayTypeNode($type));
    }
    /**
     * @param ArrayType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): Identifier
    {
        return new Identifier('array');
    }
    /**
     * Returns "(A|B)[]" as is, the union item type is split later
     */
    private function createArrayTypeNode(ArrayType $type): TypeNode
    {
        // this cannot be handled by PHPStan $type->toPhpDocNode() as requires space removal around "|" in union type
        // then e.g. "int" instead of explicit number, and nice arrays
        $itemType = $type->getIterableValueType();
        $isGenericArray = $this->isGenericArrayCandidate($type);
        if ($itemType instanceof UnionType && !$isGenericArray) {
            return $this->createArrayTypeNodeFromUnionType($itemType);
        }
        if ($itemType instanceof ArrayType && $this->isGenericArrayCandidate($itemType)) {
            return $this->createGenericArrayType($type, \true);
        }
        if ($isGenericArray) {
            return $this->createGenericArrayType($type, \true);
        }
        // keep "int" key in arary<int, mixed>
        if ($type->getKeyType() instanceof IntegerType) {
            $keyTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($type->getKeyType());
            if (!$type->isList()->maybe()) {
                $nestedTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($type->getItemType());
                return new GenericTypeNode(new IdentifierTypeNode('array'), [$keyTypeNode, $nestedTypeNode]);
            }
        }
        $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($itemType);
        return new ArrayTypeNode($itemTypeNode);
    }
    /**
     * "(A|B)[]" becomes "A[]|B[]", or "array<int, A<X>|A<Y>>" when all items are generics of the same type
     */
    private function splitUnionItemType(TypeNode $typeNode): TypeNode
    {
        if (!$typeNode instanceof ArrayTypeNode || !$typeNode->type instanceof UnionTypeNode) {
            return $typeNode;
        }
        $unionTypeNode = $typeNode->type;
        if ($this->hasOnlySameGenericTypes($unionTypeNode)) {
            return new GenericTypeNode(new IdentifierTypeNode('array'), [new IdentifierTypeNode('int'), $unionTypeNode]);
        }
        $arrayTypeNodes = [];
        foreach ($unionTypeNode->types as $unionedTypeNode) {
            $arrayTypeNodes[] = new ArrayTypeNode($unionedTypeNode);
        }
        return new UnionTypeNode($arrayTypeNodes);
    }
    private function hasOnlySameGenericTypes(UnionTypeNode $unionTypeNode): bool
    {
        $genericTypeName = null;
        foreach ($unionTypeNode->types as $unionedTypeNode) {
            if (!$unionedTypeNode instanceof GenericTypeNode) {
                return \false;
            }
            if (count($unionedTypeNode->genericTypes) !== 1) {
                return \false;
            }
            $genericTypeName ??= $unionedTypeNode->type->name;
            if ($genericTypeName !== $unionedTypeNode->type->name) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Value type in "array<string, ...>" uses "array<A|B>" instead of "A[]|B[]"
     */
    private function createGenericArrayValueTypeNode(Type $itemType): TypeNode
    {
        if (!$itemType instanceof ArrayType) {
            return $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($itemType);
        }
        $arrayTypeNode = $this->createArrayTypeNode($itemType);
        if ($arrayTypeNode instanceof ArrayTypeNode && ($arrayTypeNode->type instanceof UnionTypeNode || $arrayTypeNode->type instanceof ArrayTypeNode)) {
            return new GenericTypeNode(new IdentifierTypeNode('array'), [$arrayTypeNode->type]);
        }
        return $this->splitUnionItemType($arrayTypeNode);
    }
    private function createArrayTypeNodeFromUnionType(UnionType $unionType): ArrayTypeNode
    {
        $unionedArrayType = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $typeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($unionedType);
            $unionedArrayType[(string) $typeNode] = $typeNode;
        }
        if (count($unionedArrayType) > 1) {
            return new ArrayTypeNode(new UnionTypeNode($unionedArrayType));
        }
        /** @var TypeNode $arrayType */
        $arrayType = array_shift($unionedArrayType);
        return new ArrayTypeNode($arrayType);
    }
    private function isGenericArrayCandidate(ArrayType $arrayType): bool
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
    private function createGenericArrayType(ArrayType $arrayType, bool $withKey = \false): GenericTypeNode
    {
        $itemType = $arrayType->getIterableValueType();
        $identifierTypeNode = new IdentifierTypeNode('array');
        // is class-string[] list only
        if ($this->isClassStringArrayType($arrayType)) {
            $withKey = \false;
        }
        if (!$withKey) {
            $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($itemType);
            return new GenericTypeNode($identifierTypeNode, [$itemTypeNode]);
        }
        $keyTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($arrayType->getKeyType());
        $itemTypeNode = $this->createGenericArrayValueTypeNode($itemType);
        if ($itemTypeNode instanceof UnionTypeNode && $this->isPairClassTooDetailed($itemType)) {
            $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode(new ClassStringType());
        }
        return new GenericTypeNode($identifierTypeNode, [$keyTypeNode, $itemTypeNode]);
    }
    private function isPairClassTooDetailed(Type $itemType): bool
    {
        if (!$itemType instanceof UnionType) {
            return \false;
        }
        if (!$this->genericClassStringTypeNormalizer->isAllGenericClassStringType($itemType)) {
            return \false;
        }
        return $this->detailedTypeAnalyzer->isTooDetailed($itemType);
    }
    private function isIntegerKeyAndNonNestedArray(ArrayType $arrayType): bool
    {
        if (!$arrayType->getKeyType()->isInteger()->yes()) {
            return \false;
        }
        return !$arrayType->getIterableValueType()->isArray()->yes();
    }
    private function isClassStringArrayType(ArrayType $arrayType): bool
    {
        if ($arrayType->getKeyType() instanceof MixedType) {
            return $arrayType->getIterableValueType() instanceof GenericClassStringType;
        }
        if ($arrayType->getKeyType() instanceof ConstantIntegerType) {
            return $arrayType->getIterableValueType() instanceof GenericClassStringType;
        }
        return \false;
    }
}
