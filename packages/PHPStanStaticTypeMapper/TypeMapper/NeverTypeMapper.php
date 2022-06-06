<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
/**
 * @implements TypeMapperInterface<NeverType>
 */
final class NeverTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\NeverType::class;
    }
    /**
     * @param TypeKind::* $typeKind
     * @param NeverType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, string $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        if ($typeKind === \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN) {
            return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('never');
        }
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('mixed');
    }
    /**
     * @param NeverType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, string $typeKind) : ?\PhpParser\Node
    {
        return null;
    }
}
