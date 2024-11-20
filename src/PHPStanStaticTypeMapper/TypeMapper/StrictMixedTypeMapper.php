<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\StrictMixedType;
use PHPStan\Type\Type;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<StrictMixedType>
 */
final class StrictMixedTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    /**
     * @var string
     */
    private const MIXED = 'mixed';
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getNodeClass() : string
    {
        return StrictMixedType::class;
    }
    /**
     * @param StrictMixedType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param StrictMixedType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::MIXED_TYPE)) {
            return null;
        }
        if ($typeKind === TypeKind::UNION) {
            return null;
        }
        return new Identifier(self::MIXED);
    }
}
