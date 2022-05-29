<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<HasOffsetType>
 */
final class HasOffsetTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\Accessory\HasOffsetType::class;
    }
    /**
     * @param HasOffsetType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, string $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        return new \PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode(new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('mixed'));
    }
    /**
     * @param HasOffsetType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, string $typeKind) : ?\PhpParser\Node
    {
        return new \PhpParser\Node\Name('array');
    }
}
