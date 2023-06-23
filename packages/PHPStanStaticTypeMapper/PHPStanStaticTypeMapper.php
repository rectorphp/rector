<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\HasMethodType;
use PHPStan\Type\ConditionalType;
use PHPStan\Type\ObjectShapeType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
final class PHPStanStaticTypeMapper
{
    /**
     * @var TypeMapperInterface[]
     * @readonly
     */
    private $typeMappers;
    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(iterable $typeMappers)
    {
        $this->typeMappers = $typeMappers;
    }
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (!\is_a($type, $typeMapper->getNodeClass(), \true)) {
                continue;
            }
            return $typeMapper->mapToPHPStanPhpDocTypeNode($type);
        }
        if ($type->isString()->yes()) {
            return new IdentifierTypeNode('string');
        }
        if ($type instanceof HasMethodType) {
            return new IdentifierTypeNode('object');
        }
        if ($type instanceof ConditionalType) {
            return new IdentifierTypeNode('mixed');
        }
        if ($type instanceof ObjectShapeType) {
            return new FullyQualifiedIdentifierTypeNode('stdClass');
        }
        throw new NotImplementedYetException(__METHOD__ . ' for ' . \get_class($type));
    }
    /**
     * @param TypeKind::* $typeKind
     * @return \PhpParser\Node\Name|\PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|null
     */
    public function mapToPhpParserNode(Type $type, string $typeKind)
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (!\is_a($type, $typeMapper->getNodeClass(), \true)) {
                continue;
            }
            return $typeMapper->mapToPhpParserNode($type, $typeKind);
        }
        throw new NotImplementedYetException(__METHOD__ . ' for ' . \get_class($type));
    }
}
