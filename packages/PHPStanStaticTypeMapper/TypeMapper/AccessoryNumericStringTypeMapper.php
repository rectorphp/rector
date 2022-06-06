<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<AccessoryNumericStringType>
 */
final class AccessoryNumericStringTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\Accessory\AccessoryNumericStringType::class;
    }
    /**
     * @param AccessoryNumericStringType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, string $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('string');
    }
    /**
     * @param AccessoryNumericStringType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, string $typeKind) : ?\PhpParser\Node
    {
        return new \PhpParser\Node\Name('string');
    }
}
