<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper;

use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\Accessory\HasMethodType;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
final class PHPStanStaticTypeMapper
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface[]
     */
    private $typeMappers;
    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(array $typeMappers)
    {
        $this->typeMappers = $typeMappers;
    }
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (!\is_a($type, $typeMapper->getNodeClass(), \true)) {
                continue;
            }
            return $typeMapper->mapToPHPStanPhpDocTypeNode($type, $typeKind);
        }
        if ($type instanceof \PHPStan\Type\Accessory\AccessoryNumericStringType) {
            return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('string');
        }
        if ($type instanceof \PHPStan\Type\Accessory\HasMethodType) {
            return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('object');
        }
        throw new \Rector\Core\Exception\NotImplementedYetException(__METHOD__ . ' for ' . \get_class($type));
    }
    /**
     * @return \PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|null
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind $typeKind)
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (!\is_a($type, $typeMapper->getNodeClass(), \true)) {
                continue;
            }
            return $typeMapper->mapToPhpParserNode($type, $typeKind);
        }
        throw new \Rector\Core\Exception\NotImplementedYetException(__METHOD__ . ' for ' . \get_class($type));
    }
}
