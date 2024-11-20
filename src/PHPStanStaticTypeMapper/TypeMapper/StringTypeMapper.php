<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<StringType>
 */
final class StringTypeMapper implements TypeMapperInterface
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
        return StringType::class;
    }
    /**
     * @param StringType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        return new Identifier('string');
    }
}
