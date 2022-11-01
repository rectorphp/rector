<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix202211\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements TypeMapperInterface<GenericClassStringType>
 */
final class GenericClassStringTypeMapper implements TypeMapperInterface
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
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
     * @required
     */
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return GenericClassStringType::class;
    }
    /**
     * @param GenericClassStringType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        $attributeAwareIdentifierTypeNode = new IdentifierTypeNode('class-string');
        $genericType = $this->resolveGenericObjectType($type);
        $genericTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($genericType, $typeKind);
        return new GenericTypeNode($attributeAwareIdentifierTypeNode, [$genericTypeNode]);
    }
    /**
     * @param GenericClassStringType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        return new Name('string');
    }
    /**
     * @return \PHPStan\Type\ObjectType|\PHPStan\Type\Type
     */
    private function resolveGenericObjectType(GenericClassStringType $genericClassStringType)
    {
        $genericType = $genericClassStringType->getGenericType();
        if (!$genericType instanceof ObjectType) {
            return $genericType;
        }
        $className = $genericType->getClassName();
        $className = $this->normalizeType($className);
        return new ObjectType($className);
    }
    private function normalizeType(string $classType) : string
    {
        if (\is_a($classType, Expr::class, \true)) {
            return Expr::class;
        }
        if (\is_a($classType, Node::class, \true)) {
            return Node::class;
        }
        return $classType;
    }
}
