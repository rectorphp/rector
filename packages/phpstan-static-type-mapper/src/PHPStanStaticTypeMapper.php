<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedException;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class PHPStanStaticTypeMapper
{
    /**
     * @var string
     */
    public const KIND_PARAM = 'param';

    /**
     * @var string
     */
    public const KIND_PROPERTY = 'property';

    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers = [];

    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(array $typeMappers)
    {
        $this->typeMappers = $typeMappers;
    }

    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPHPStanPhpDocTypeNode($type);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($type));
    }

    /**
     * @return Name|NullableType|PhpParserUnionType|null
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPhpParserNode($type, $kind);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($type));
    }

    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToDocString($type, $parentType);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($type));
    }
}
