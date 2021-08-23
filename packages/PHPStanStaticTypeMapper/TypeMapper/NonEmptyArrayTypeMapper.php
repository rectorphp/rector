<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
/**
 * @implements TypeMapperInterface<NonEmptyArrayType>
 */
final class NonEmptyArrayTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\Accessory\NonEmptyArrayType::class;
    }
    /**
     * @param \PHPStan\Type\Type $type
     * @param \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind $typeKind
     */
    public function mapToPHPStanPhpDocTypeNode($type, $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        return new \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode(new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('mixed'));
    }
    /**
     * @param \PHPStan\Type\Type $type
     * @param \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind $typeKind
     */
    public function mapToPhpParserNode($type, $typeKind) : ?\PhpParser\Node
    {
        return new \PhpParser\Node\Name('array');
    }
}
