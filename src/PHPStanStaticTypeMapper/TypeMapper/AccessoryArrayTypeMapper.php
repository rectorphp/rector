<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\HasOffsetValueType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\Accessory\OversizedArrayType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * Every accessory array type narrows "array" with an extra guarantee, so they all map back to "array"
 *
 * @implements TypeMapperInterface<HasOffsetType|HasOffsetValueType|NonEmptyArrayType|OversizedArrayType>
 */
final class AccessoryArrayTypeMapper implements TypeMapperInterface
{
    /**
     * @return array<class-string<Type>>
     */
    public function getNodeClasses(): array
    {
        return [HasOffsetType::class, HasOffsetValueType::class, NonEmptyArrayType::class, OversizedArrayType::class];
    }
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    public function mapToPhpParserNode(Type $type, string $typeKind): Identifier
    {
        return new Identifier('array');
    }
}
