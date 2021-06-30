<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;

/**
 * @implements TypeMapperInterface<NullType>
 */
final class NullTypeMapper implements TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass(): string
    {
        return NullType::class;
    }

    /**
     * @param NullType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, ?string $kind = null): TypeNode
    {
        return new IdentifierTypeNode('null');
    }

    /**
     * @param NullType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if ($kind !== TypeKind::KIND_PROPERTY) {
            return null;
        }

        return new Name('null');
    }
}
