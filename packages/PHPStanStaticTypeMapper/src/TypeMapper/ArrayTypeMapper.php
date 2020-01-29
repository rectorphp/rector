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

final class ArrayTypeMapper implements TypeMapperInterface
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    /**
     * @required
     */
    public function autowireArrayTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
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

        if ($itemType instanceof UnionType) {
            return $this->mapArrayUnionTypeToDocString($type, $itemType);
        }

        $docString = $this->phpStanStaticTypeMapper->mapToDocString($type->getItemType(), $parentType);

        // @todo improve this
        $docStringTypes = explode('|', $docString);
        $docStringTypes = array_filter($docStringTypes);

        foreach ($docStringTypes as $key => $docStringType) {
            $docStringTypes[$key] = $docStringType . '[]';
        }

        return implode('|', $docStringTypes);
    }

    private function mapArrayUnionTypeToDocString(ArrayType $arrayType, UnionType $unionType): string
    {
        $unionedTypesAsString = [];

        foreach ($unionType->getTypes() as $unionedArrayItemType) {
            $unionedTypesAsString[] = $this->phpStanStaticTypeMapper->mapToDocString(
                $unionedArrayItemType,
                $arrayType
            ) . '[]';
        }

        $unionedTypesAsString = array_values($unionedTypesAsString);
        $unionedTypesAsString = array_unique($unionedTypesAsString);

        return implode('|', $unionedTypesAsString);
    }

    /**
     * @todo improve
     */
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
}
