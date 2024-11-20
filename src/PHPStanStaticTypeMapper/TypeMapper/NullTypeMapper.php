<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<NullType>
 */
final class NullTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getNodeClass() : string
    {
        return NullType::class;
    }
    /**
     * @param NullType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param TypeKind::* $typeKind
     * @param NullType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        // can be a standalone type, only case where null makes sense
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::NULL_FALSE_TRUE_STANDALONE_TYPE) && $typeKind === TypeKind::RETURN) {
            return new Identifier('null');
        }
        // if part of union, can be added even in PHP 8.0
        if ($typeKind === TypeKind::UNION && $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::NULLABLE_TYPE)) {
            return new Identifier('null');
        }
        return null;
    }
}
