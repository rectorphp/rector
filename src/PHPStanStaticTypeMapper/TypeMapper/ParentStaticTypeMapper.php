<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\Enum\ObjectReference;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
/**
 * @implements TypeMapperInterface<ParentStaticType>
 */
final class ParentStaticTypeMapper implements TypeMapperInterface
{
    public function getNodeClass() : string
    {
        return ParentStaticType::class;
    }
    /**
     * @param ParentStaticType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param ParentStaticType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : Name
    {
        return new Name(ObjectReference::PARENT);
    }
}
