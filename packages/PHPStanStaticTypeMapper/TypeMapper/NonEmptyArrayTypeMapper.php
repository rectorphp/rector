<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeMapper;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\Type\Accessory\NonEmptyArrayType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<NonEmptyArrayType>
 */
final class NonEmptyArrayTypeMapper implements TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return NonEmptyArrayType::class;
    }
    /**
     * @param NonEmptyArrayType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        return new SpacingAwareArrayTypeNode(new IdentifierTypeNode('mixed'));
    }
    /**
     * @param NonEmptyArrayType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        return new Name('array');
    }
}
