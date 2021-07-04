<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\StrictMixedType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;

/**
 * @implements TypeMapperInterface<StrictMixedType>
 */
final class StrictMixedTypeMapper implements TypeMapperInterface
{
    /**
     * @var string
     */
    private const MIXED = 'mixed';

    /**
     * @return class-string<Type>
     */
    public function getNodeClass(): string
    {
        return StrictMixedType::class;
    }

    /**
     * @param StrictMixedType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, TypeKind $typeKind): TypeNode
    {
        return new IdentifierTypeNode(self::MIXED);
    }

    /**
     * @param StrictMixedType $type
     */
    public function mapToPhpParserNode(Type $type, TypeKind $typeKind): ?Node
    {
        return new Name(self::MIXED);
    }
}
