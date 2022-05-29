<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<AccessoryNonEmptyStringType>
 */
final class AccessoryNonEmptyStringTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\Accessory\AccessoryNonEmptyStringType::class;
    }
    /**
     * @param AccessoryNonEmptyStringType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, string $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('string');
    }
    /**
     * @param AccessoryNonEmptyStringType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, string $typeKind) : ?\PhpParser\Node
    {
        return new \PhpParser\Node\Name('string');
    }
}
