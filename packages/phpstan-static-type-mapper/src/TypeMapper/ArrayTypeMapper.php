<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareGenericTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\TypeNormalizer;

final class ArrayTypeMapper implements TypeMapperInterface
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    /**
     * @var TypeNormalizer
     */
    private $typeNormalizer;

    /**
     * @required
     */
    public function autowireArrayTypeMapper(
        PHPStanStaticTypeMapper $phpStanStaticTypeMapper,
        TypeNormalizer $typeNormalizer
    ): void {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->typeNormalizer = $typeNormalizer;
    }

    public function getNodeClass(): string
    {
        return ArrayType::class;
    }

    /**
     * @param ArrayType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($type->getItemType());

        if ($itemTypeNode instanceof UnionTypeNode) {
            return $this->convertUnionArrayTypeNodesToArrayTypeOfUnionTypeNodes($itemTypeNode);
        }

        if ($this->isGenericArrayCandidate($type)) {
            return $this->createGenericArrayType($type->getKeyType(), $itemTypeNode);
        }

        return new ArrayTypeNode($itemTypeNode);
    }

    /**
     * @param ArrayType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        return new Name('array');
    }

    /**
     * @param ArrayType $type
     */
    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        $itemType = $type->getItemType();

        $normalizedType = $this->typeNormalizer->normalizeArrayOfUnionToUnionArray($type);
        if ($normalizedType instanceof UnionType) {
            return $this->mapArrayUnionTypeToDocString($type, $normalizedType);
        }

        return $this->phpStanStaticTypeMapper->mapToDocString($itemType, $parentType) . '[]';
    }

    private function convertUnionArrayTypeNodesToArrayTypeOfUnionTypeNodes(
        UnionTypeNode $unionTypeNode
    ): AttributeAwareUnionTypeNode {
        $unionedArrayType = [];
        foreach ($unionTypeNode->types as $unionedType) {
            if ($unionedType instanceof UnionTypeNode) {
                foreach ($unionedType->types as $key => $subUnionedType) {
                    $unionedType->types[$key] = new ArrayTypeNode($subUnionedType);
                }

                $unionedArrayType[] = $unionedType;
                continue;
            }

            $unionedArrayType[] = new ArrayTypeNode($unionedType);
        }

        return new AttributeAwareUnionTypeNode($unionedArrayType);
    }

    private function isGenericArrayCandidate(ArrayType $arrayType): bool
    {
        if ($arrayType->getKeyType() instanceof MixedType) {
            return false;
        }

        // skip simple arrays, like "string[]", from converting to obvious "array<int, string>"
        if ($this->isIntegerKeyAndNonNestedArray($arrayType)) {
            return false;
        }

        if ($arrayType->getKeyType() instanceof NeverType) {
            return false;
        }

        // make sure the integer key type is not natural/implicit array int keys
        $keysArrayType = $arrayType->getKeysArray();
        if (! $keysArrayType instanceof ConstantArrayType) {
            return true;
        }

        foreach ($keysArrayType->getValueTypes() as $key => $keyType) {
            if (! $keyType instanceof ConstantIntegerType) {
                return true;
            }

            if ($key !== $keyType->getValue()) {
                return true;
            }
        }

        return false;
    }

    private function createGenericArrayType(Type $keyType, TypeNode $itemTypeNode): AttributeAwareGenericTypeNode
    {
        /** @var IdentifierTypeNode $keyTypeNode */
        $keyTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($keyType);

        $attributeAwareIdentifierTypeNode = new AttributeAwareIdentifierTypeNode('array');

        // @see https://github.com/phpstan/phpdoc-parser/blob/98a088b17966bdf6ee25c8a4b634df313d8aa531/tests/PHPStan/Parser/PhpDocParserTest.php#L2692-L2696
        $genericTypes = [$keyTypeNode, $itemTypeNode];
        return new AttributeAwareGenericTypeNode($attributeAwareIdentifierTypeNode, $genericTypes);
    }

    private function mapArrayUnionTypeToDocString(ArrayType $arrayType, UnionType $unionType): string
    {
        $unionedTypesAsString = [];

        foreach ($unionType->getTypes() as $unionedArrayItemType) {
            $unionedTypesAsString[] = $this->phpStanStaticTypeMapper->mapToDocString(
                $unionedArrayItemType,
                $arrayType
            );
        }

        $unionedTypesAsString = array_values($unionedTypesAsString);
        $unionedTypesAsString = array_unique($unionedTypesAsString);

        return implode('|', $unionedTypesAsString);
    }

    private function isIntegerKeyAndNonNestedArray(ArrayType $arrayType): bool
    {
        if (! $arrayType->getKeyType() instanceof IntegerType) {
            return false;
        }

        return ! $arrayType->getItemType() instanceof ArrayType;
    }
}
