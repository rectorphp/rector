<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Type;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<BooleanType>
 */
final class BooleanTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return BooleanType::class;
    }
    /**
     * @param BooleanType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        if ($type instanceof ConstantBooleanType) {
            return new IdentifierTypeNode($type->getValue() ? 'true' : 'false');
        }
        return new IdentifierTypeNode('bool');
    }
    /**
     * @param BooleanType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::NULL_FALSE_TRUE_STANDALONE_TYPE)) {
            return new Identifier('bool');
        }
        if (!$type instanceof ConstantBooleanType) {
            return new Identifier('bool');
        }
        return $type->getValue() ? new Identifier('true') : new Identifier('false');
    }
}
