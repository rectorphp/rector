<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
/**
 * @implements TypeMapperInterface<ClosureType>
 */
final class ClosureTypeMapper implements TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return ClosureType::class;
    }
    /**
     * @param ClosureType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param TypeKind::* $typeKind
     * @param ClosureType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if ($typeKind === TypeKind::PROPERTY) {
            return null;
        }
        return new FullyQualified('Closure');
    }
}
