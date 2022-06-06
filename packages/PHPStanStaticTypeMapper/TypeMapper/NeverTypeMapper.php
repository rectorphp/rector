<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeMapper;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\Type\NeverType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
/**
 * @implements TypeMapperInterface<NeverType>
 */
final class NeverTypeMapper implements TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return NeverType::class;
    }
    /**
     * @param TypeKind::* $typeKind
     * @param NeverType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        if ($typeKind === TypeKind::RETURN) {
            return new IdentifierTypeNode('never');
        }
        return new IdentifierTypeNode('mixed');
    }
    /**
     * @param NeverType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        return null;
    }
}
