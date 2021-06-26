<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper;

use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
final class PHPStanStaticTypeMapper
{
    /**
     * @var mixed[]
     */
    private $typeMappers;
    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(array $typeMappers)
    {
        $this->typeMappers = $typeMappers;
    }
    /**
     * @param TypeKind::*|null $kind
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, ?string $kind = null) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (!\is_a($type, $typeMapper->getNodeClass(), \true)) {
                continue;
            }
            return $typeMapper->mapToPHPStanPhpDocTypeNode($type, $kind);
        }
        throw new \Rector\Core\Exception\NotImplementedYetException(__METHOD__ . ' for ' . \get_class($type));
    }
    /**
     * @param TypeKind::*|null $kind
     * @return \PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|null
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, ?string $kind = null)
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (!\is_a($type, $typeMapper->getNodeClass(), \true)) {
                continue;
            }
            return $typeMapper->mapToPhpParserNode($type, $kind);
        }
        throw new \Rector\Core\Exception\NotImplementedYetException(__METHOD__ . ' for ' . \get_class($type));
    }
}
