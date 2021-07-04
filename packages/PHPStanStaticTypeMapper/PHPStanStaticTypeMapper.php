<?php

declare(strict_types=1);

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
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(
        private array $typeMappers
    ) {
    }

    public function mapToPHPStanPhpDocTypeNode(Type $type, TypeKind $typeKind): TypeNode
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPHPStanPhpDocTypeNode($type, $typeKind);
        }

        throw new NotImplementedYetException(__METHOD__ . ' for ' . $type::class);
    }

    public function mapToPhpParserNode(Type $type, TypeKind $typeKind): Name | NullableType | UnionType | null
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPhpParserNode($type, $typeKind);
        }

        throw new NotImplementedYetException(__METHOD__ . ' for ' . $type::class);
    }
}
