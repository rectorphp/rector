<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;

/**
 * @implements TypeMapperInterface<MixedType>
 */
final class MixedTypeMapper implements TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass(): string
    {
        return MixedType::class;
    }

    /**
     * @param MixedType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, TypeKind $typeKind): TypeNode
    {
        return new IdentifierTypeNode('mixed');
    }

    /**
     * @param MixedType $type
     */
    public function mapToPhpParserNode(Type $type, TypeKind $typeKind): ?Node
    {
        return null;
    }
}
