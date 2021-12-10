<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\StrictMixedType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
/**
 * @implements TypeMapperInterface<StrictMixedType>
 */
final class StrictMixedTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @var string
     */
    private const MIXED = 'mixed';
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\StrictMixedType::class;
    }
    /**
     * @param StrictMixedType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode(self::MIXED);
    }
    /**
     * @param StrictMixedType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind) : ?\PhpParser\Node
    {
        return new \PhpParser\Node\Name(self::MIXED);
    }
}
