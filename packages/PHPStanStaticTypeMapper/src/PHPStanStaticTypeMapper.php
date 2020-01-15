<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\Exception\NotImplementedException;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class PHPStanStaticTypeMapper
{
    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers = [];

    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(array $typeMappers)
    {
        $this->typeMappers = $typeMappers;
    }

    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        // @todo move to ArrayTypeMapper
        if ($type instanceof ArrayType) {
            $itemTypeNode = $this->mapToPHPStanPhpDocTypeNode($type->getItemType());
            if ($itemTypeNode instanceof UnionTypeNode) {
                return $this->convertUnionArrayTypeNodesToArrayTypeOfUnionTypeNodes($itemTypeNode);
            }

            return new ArrayTypeNode($itemTypeNode);
        }

        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPHPStanPhpDocTypeNode($type);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($type));
    }

    /**
     * @return Identifier|Name|NullableType|PhpParserUnionType|null
     */
    public function mapToPhpParserNode(Type $phpStanType, ?string $kind = null): ?Node
    {
        foreach ($this->typeMappers as $typeMapper) {
            // it cannot be is_a for SelfObjectType, because type classes inherit from each other
            if (! is_a($phpStanType, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPhpParserNode($phpStanType, $kind);
        }

        if ($phpStanType instanceof ArrayType) {
            return new Identifier('array');
        }

        if ($phpStanType instanceof StaticType) {
            return null;
        }

        if ($phpStanType instanceof TypeWithClassName) {
            $lowerCasedClassName = strtolower($phpStanType->getClassName());
            if ($lowerCasedClassName === 'callable') {
                return new Identifier('callable');
            }

            if ($lowerCasedClassName === 'self') {
                return new Identifier('self');
            }

            if ($lowerCasedClassName === 'static') {
                return null;
            }

            if ($lowerCasedClassName === 'mixed') {
                return null;
            }

            return new FullyQualified($phpStanType->getClassName());
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpStanType));
    }

    public function mapToDocString(Type $phpStanType, ?Type $parentType = null): string
    {
        foreach ($this->typeMappers as $typeMapper) {
            // it cannot be is_a for SelfObjectType, because type classes inherit from each other
            if (! is_a($phpStanType, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToDocString($phpStanType, $parentType);
        }

        if ($phpStanType instanceof ArrayType) {
            if ($phpStanType->getItemType() instanceof UnionType) {
                $unionedTypesAsString = [];
                foreach ($phpStanType->getItemType()->getTypes() as $unionedArrayItemType) {
                    $unionedTypesAsString[] = $this->mapToDocString($unionedArrayItemType, $phpStanType) . '[]';
                }

                $unionedTypesAsString = array_values($unionedTypesAsString);
                $unionedTypesAsString = array_unique($unionedTypesAsString);

                return implode('|', $unionedTypesAsString);
            }

            $docString = $this->mapToDocString($phpStanType->getItemType(), $parentType);

            // @todo improve this
            $docStringTypes = explode('|', $docString);
            $docStringTypes = array_filter($docStringTypes);

            foreach ($docStringTypes as $key => $docStringType) {
                $docStringTypes[$key] = $docStringType . '[]';
            }

            return implode('|', $docStringTypes);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpStanType));
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
}
