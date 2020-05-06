<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
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

        return new ArrayTypeNode($itemTypeNode);
    }

    /**
     * @param ArrayType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        return new Identifier('array');
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
}
