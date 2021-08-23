<?php

declare (strict_types=1);
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
final class MixedTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\MixedType::class;
    }
    /**
     * @param \PHPStan\Type\Type $type
     * @param \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind $typeKind
     */
    public function mapToPHPStanPhpDocTypeNode($type, $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('mixed');
    }
    /**
     * @param \PHPStan\Type\Type $type
     * @param \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind $typeKind
     */
    public function mapToPhpParserNode($type, $typeKind) : ?\PhpParser\Node
    {
        return null;
    }
}
