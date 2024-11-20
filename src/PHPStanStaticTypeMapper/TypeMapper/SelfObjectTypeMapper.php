<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
/**
 * @implements TypeMapperInterface<SelfObjectType>
 */
final class SelfObjectTypeMapper implements TypeMapperInterface
{
    public function getNodeClass() : string
    {
        return SelfObjectType::class;
    }
    /**
     * @param SelfObjectType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param SelfObjectType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : Name
    {
        return new Name('self');
    }
}
