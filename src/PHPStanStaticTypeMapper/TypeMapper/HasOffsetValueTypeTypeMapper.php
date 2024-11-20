<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\HasOffsetValueType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<HasOffsetValueType>
 */
final class HasOffsetValueTypeTypeMapper implements TypeMapperInterface
{
    public function getNodeClass() : string
    {
        return HasOffsetValueType::class;
    }
    /**
     * @param HasOffsetValueType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param HasOffsetValueType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : Identifier
    {
        return new Identifier('array');
    }
}
