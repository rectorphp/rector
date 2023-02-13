<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\FloatType;
use PHPStan\Type\Type;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<FloatType>
 */
final class FloatTypeMapper implements TypeMapperInterface
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
        return FloatType::class;
    }
    /**
     * @param FloatType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        return new IdentifierTypeNode('float');
    }
    /**
     * @param FloatType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        return new Identifier('float');
    }
}
