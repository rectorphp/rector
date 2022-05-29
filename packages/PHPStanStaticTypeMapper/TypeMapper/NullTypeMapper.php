<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
/**
 * @implements TypeMapperInterface<NullType>
 */
final class NullTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\NullType::class;
    }
    /**
     * @param NullType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, string $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('null');
    }
    /**
     * @param TypeKind::* $typeKind
     * @param NullType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, string $typeKind) : ?\PhpParser\Node
    {
        if ($typeKind === \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY) {
            return null;
        }
        if ($typeKind === \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM) {
            return null;
        }
        // return type cannot be only null
        if ($typeKind === \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN) {
            return null;
        }
        return new \PhpParser\Node\Name('null');
    }
}
