<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ResourceType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;

/**
 * @implements TypeMapperInterface<ResourceType>
 */
final class ResourceTypeMapper implements TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass(): string
    {
        return ResourceType::class;
    }

    /**
     * @param ResourceType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, TypeKind $typeKind): TypeNode
    {
        return new IdentifierTypeNode('resource');
    }

    /**
     * @param ResourceType $type
     */
    public function mapToPhpParserNode(Type $type, TypeKind $typeKind): ?Node
    {
        return null;
    }
}
